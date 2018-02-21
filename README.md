EntityGeneratorBundle
=====================

**WORK IN PROGRESS: major changes are constantly being made at this moment**


Bundle to interactively generate entities in Symfony 4. 

This generator allows you to create entities and update existing
entities.

Features:

- Three console commands:
    - `entity:generate` to create a new entity.
    - `entity:append` to safely add fields to an existing entity without overwriting anything else.
    - `entity:alter` command to load content of an existing entity and allows you to
    edit that very content.
- Supports lots of ORM types, including 
ManyToOne, OneToMany, ManyToMany and OneToOne relationships.
- Automatically creates or updates targetEntity when mappedBy or InversedBy
is being used.
- Optionally add validation to fields.
- Supports multiple bundles, as well the bundleless 'App' namespace.
- Optionally specify subdirectories.
- Interactive command allows you to edit your choices to easily fix
mistakes or typos.
- Extensibility: override almost any file by simply extending a class, implementing an interface
or replacing a service.

## Attributes configuration
Some questions may get annoying real quick. For instance, if you always use a standard $id property
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
The entity is generated through twig-files, which you can overwrite or
extend by adding files with identical names to any of the following directories:
- `{projectDir}/templates/bundles/EntityGeneratorBundle/skeleton`
- `{projectDir}/EntityGenerator/skeleton`
- `{projectDir}/EntityGeneratorBundle/skeleton`

Instead of either of these options, you can also specify any skeleton-location
in the configuration option `entity_generator.override_skeleton_path`. 
For example:


    entity_generator:
        override_skeleton_path: '%kernel.root_dir%/Generators/EntityGenerator/skeleton/'
        

If you want to extend one of the files, you could for example add a file
`_traits.php.twig` to your skeleton-dir, with the following content:

    {% use '@EntityGeneratorBundle/_traits.php.twig' %}
    {% block traits %}
        use MySpecificEntityTraitThatShouldAlwaysBeIncluded;
        {{ parent() }}
    {% endblock %}

This way you can add a trait which will always be included, while other traits
are still managed by the generator itself.