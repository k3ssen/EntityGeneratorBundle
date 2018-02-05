EntityGeneratorBundle
=====================

Bundle to interactively generate entities in Symfony4. 

**WORK IN PROGRESS: currently not suitable for any usage**



## Extensibility
One that fits all. Such generator probably won't exist. 

There are quite a few generators created for symfony projects, but 
every project or developer has different needs, 
so there won't be any generator that will fit everyone's needs. 

With that in mind this generator is created to be extensible: many components
are used as services, enabling you to extend or overwrite them.


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