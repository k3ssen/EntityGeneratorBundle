EntityGeneratorBundle
=====================

> ## This bundle is no longer maintained and will be removed in a while. Please checkout the [GeneratorBundle](https://github.com/k3ssen/GeneratorBundle) instead.

**~~WORK IN PROGRES: major changes are constantly being made at this moment~~**


Bundle to interactively generate entities in Symfony 4. 

This generator allows you to create entities and update existing
entities.

Features:

- console commands:
    - `entity:generate` to create a new entity.
    - `entity:append` to safely add fields to an existing entity without overwriting anything else.
    - `entity:alter` command to load content of an existing entity and allows you to
    - `entity:skeleton-override` command to create skeleton files that extend the skeleton of this 
    EntityGeneratorBundle, so to can quickly make adjustments to the generator.
- Supports lots of ORM types, including 
ManyToOne, OneToMany, ManyToMany and OneToOne relationships.
- Automatically creates or updates targetEntity when mappedBy or InversedBy
is being used.
- Optionally add validation to fields.
- Supports multiple bundles, as well the bundleless 'App' namespace, even if there're combined.
- Optionally specify subdirectories.
- Interactive command allows you to edit your choices to easily fix
mistakes or typos.
- Extensibility: override almost any file by simply extending a class, implementing an interface
or replacing a service.

## Attributes configuration
Some questions may get annoying real quick. For instance, if you always use the standard $id property
for all your entities, then getting asked if a property is an id is quite useless. 
Below is an example of how to disable questions for several attributes.

    entity_generator:
        attributes:
            id:
                question: null
            unique:
                question: null
            nullable:
                question: null
            length:
                question: null
            precision:
                question: null
            scale:
                question: null
            referencedColumnName:
                question: null
            orphanRemoval:
                question: null

## Extensibility
This generator is created to support what is expected to
be useful for entities. 

Because it is hard to predict what could be useful to others,
 this generator is created to be extensible: many components
are created as services or configurable classes, 
enabling you to extend or overwrite nearly everything without needing
to rewrite the whole codebase.


`TODO: documentation for examples`

* Overriding skeleton (twig files)
* Meta data  
The Meta data classes are loaded trough Factories. The factories are injected
as services, which you can override, for instance to use different
classes that you've defined.

### Overriding skeleton
The final result is being generated through twig-files, which you can easily extend or overwrite.
See https://symfony.com/doc/current/templating/overriding.html for info about overriding twig files.

#### Usage example:
Lets say you want all your entities to include a trait called 'MyTraitThatIsHelpfulForEntities', located
in 'src/Entity/Traits'.

A simple way would be to create a file `templates/bundles/EntityGeneratorBundle/skeleton/entity.php`
with the following content:

    {% extends '@!EntityGenerator/skeleton/entity.php.twig' %}
    {% block usages %}{{ parent() -}}
    use App\Entity\Traits\MyTraitThatIsHelpfulForEntities;
    {% endblock %}
    {% block traits %}{{ parent() }}
        use MyTraitThatIsHelpfulForEntities;
    {% endblock %}

If you need to access data you can use the `meta_entity`, which is an instance of the MetaDataInterface.
Furthermore, the `entity.php.twig` file uses several other twig files to keep things separate, so if
you're in need of making lots of alterations, you might want to override those files instead. 
Just have a look at the files in 
[EntityGeneratorBundle/Resources/views/skeleton](./Resources/views/skeleton) 
to get a feeling of what you could use.


#### Command `entity:skeleton-override`
It's bothersome to search files in a vendor directory just to know where to start for overriding files.

Using the command `php bin/console entity:skeleton-override`, the twig-files that extend the EntityGeneratorBundle are automatically
created inside the templates directory of your project.
This will give you a quick start for making any alterations you want.
