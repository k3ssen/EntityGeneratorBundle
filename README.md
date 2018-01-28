EntityGeneratorBundle
=====================

Bundle to interactively generate entities in Symfony4. 

**WORK IN PROGRESS: currently not suitable for any usage**





## Extensibility
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
extend by adding files with identical names to 
`{projectDir}/templates/bundles/EntityGeneratorBundle/skeleton`.

**TODO:** when someone needs to extend this bundle, it might be
nice to have everything bundled together. For instance, a directory 
'EntityGenerator' that contains all extensions.
So in addition to overriding the
skeleton in the templates directory, it should be possible to override
the skeleton in a custom defined directory.