# Install

composer.json:
```
    "gedmo/doctrine-extensions": "~2.3"
    "sonata/loggable-entity-bundle": "master@dev"
```

composer update

app/AppKernel.php:
```
    new Core\LoggableEntityBundle\LoggableEntityBundle(),
```

app/config.yml:
```
    entity_managers:
        default:
            auto_mapping: true
            mappings:
                loggable:
                    type: annotation
                    alias: Loggable
                    prefix: Gedmo\Loggable\Entity
                    # make sure vendor library location is correct
                    dir: "%kernel.root_dir%/../vendor/gedmo/doctrine-extensions/lib/Gedmo/Loggable/Entity"
```
app/config/dictrine_extensions.yml:
```
gedmo.listener.loggable:
    class: %core.loggable_entity.loggable_listener.class%
    calls:
      - [setService, [@core.loggable_entity.service]]
    tags:
      - { name: doctrine.event_subscriber, connection: default, priority: 0 }
```
vagy kitörölni belőle a teljes gedmo.listener.loggable service-t


# Entitás:

```
AppBundle\Entity\xyz:
    type: entity
    table:
    id:
        id:
            type: integer
            generator:
                strategy:   AUTO
    gedmo:
      loggable:
        logEntryClass: Core\LoggableEntityBundle\Entity\LogEntry

    fields:
        xyz:
            type: string
            length: 255
            nullable: false
            gedmo:
                - versioned
```

Az entitás adminjára pedig:
```
    class xyzAdmin extends Admin implements EntityLogHistoryAdminInterface
```

Ha azt akarjuk, hogy az entitáshoz lehessen megjegyzést hozzáfűzni, akkor az entitás osztályán:

```
class xyz implements LogExtraDataAware {
    use LogExtraDataAwareTrait;
}
```