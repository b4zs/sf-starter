<?php
namespace Core\LoggableEntityBundle\Enum;


class LogNoteTypesEnum
{
    const PHONE_CALL = 'phone call';

    const EMAIL = 'email';

    const MEETING = 'meeting';

    /**
     * @return array
     */
    public static function getChoices() {
        return array(
            static::PHONE_CALL => 'form.label.phone_call',
            static::EMAIL => 'form.label.email',
            static::MEETING => 'form.label.meeting',
        );
    }

}