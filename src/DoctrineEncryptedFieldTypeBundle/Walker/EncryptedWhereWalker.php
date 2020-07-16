<?php

namespace DoctrineEncryptedFieldTypeBundle\Walker;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\AST;
use Doctrine\ORM\Query\SqlWalker;
use DoctrineEncryptedFieldTypeBundle\Encryptor\EncryptorInterface;
use DoctrineEncryptedFieldTypeBundle\Encryptor\Hasher;

/**
 * @property EncryptorInterface $encryptor
 * This class violates the extendable behaviour of the Encrypted field encoding/decoding,
 * as it uses the Hasher class directly (even the MD5 hashing solution as well)
 */
class EncryptedWhereWalker extends SqlWalker
{
    /**
     * @deprecated
     *
     * Processes LIKE expressions and handles search for some encrypted fields,
     * eg (a.b LIKE '%:param%') can have results, but only in case of exact match (due to hashing)
     *
     * This method violates the process of DQL/SQL conversion
     * as it changes the value of the PARAMETER instead of the generated SQL. This causes tricky bugs when
     * the query cache is being used, as in that case, the parameters are not getting processed.
     */
    public function walkLikeExpression($likeExpr)
    {
        /** @var AST\LikeExpression $likeExpr */
        if ($likeExpr instanceof AST\LikeExpression) {
            if (!empty($likeExpr->stringExpression->identificationVariable) && !$likeExpr->stringPattern instanceof AST\Literal) {
                $tableAlias = $likeExpr->stringExpression->identificationVariable;
                $fieldAlias = $likeExpr->stringPattern->name;
                $fieldName  = $likeExpr->stringExpression->field;
                $fieldType = $this->getFieldTypeByTableAndFieldAlias($tableAlias, $fieldName);

                $parameter = $this->getQuery()->getParameter($fieldAlias);

                if (in_array($fieldType, ['encrypted_data_string', 'encrypted_data_datetime', 'encrypted_data_text'])) {
                    $this->getQuery()->setCacheable(false);
                    $cleartextValue = preg_replace('/^%(.*)%$/', '$1', $parameter->getValue());

                    // TODO: inject Encryptor
                    $parameter->setValue('%' . Hasher::hash($cleartextValue) . '%');
                }
            }
        }

        $sql = parent::walkLikeExpression($likeExpr);

        return $sql;
    }

    /**
     * Processes ComparisonExpressions (a.b = :param)
     * It does it by catching equity comparisions on encrypted fields
     * and wrapping the RIGHT expression and overwriting the operator.
     * Eg. (pseudo code)
     *   a.b = ?
     * becomes
     *   a.b LIKE ('%'+MD5(?)+'%')
     */
    public function walkComparisonExpression($compExpr)
    {
        $leftExpr  = $compExpr->leftExpression;
        $rightExpr = $compExpr->rightExpression;

        $sql = ($leftExpr instanceof AST\Node)
            ? $leftExpr->dispatch($this)
            : (is_numeric($leftExpr) ? $leftExpr : $this->getConnection()->quote($leftExpr));

        $operatorSql = $compExpr->operator;

        $rightExprSql = ($rightExpr instanceof AST\Node)
            ? $rightExpr->dispatch($this)
            : (is_numeric($rightExpr) ? $rightExpr : $this->getConnection()->quote($rightExpr));

        if ($leftExpr instanceof AST\ArithmeticExpression) {
            if ($leftExpr->simpleArithmeticExpression instanceof AST\PathExpression) {
                $tableAlias = $leftExpr->simpleArithmeticExpression->identificationVariable;
                $fieldName = $leftExpr->simpleArithmeticExpression->field;
                $fieldType = $this->getFieldTypeByTableAndFieldAlias($tableAlias, $fieldName);

                if (in_array($fieldType, ['encrypted_data_string', 'encrypted_data_datetime', 'encrypted_data_text'])) {
                    /**  @see \DoctrineEncryptedFieldTypeBundle\Encryptor\Hasher::hash */
                    $operatorSql = 'LIKE';
                    $rightExprSql = sprintf('CONCAT(CONCAT("%%hash=", MD5(%s)), "%%")', $rightExprSql);
                }
            }
        }

        $sql .= ' ' . $operatorSql . ' ' . $rightExprSql;

        return $sql;
    }

    private function getFieldTypeByTableAndFieldAlias($tableAlias, $fieldName)
    {
        /** @var ClassMetadata $metadata */
        $metadata = $this->getQueryComponent($tableAlias)['metadata'];

        return $metadata->getTypeOfField($fieldName);
    }
}
