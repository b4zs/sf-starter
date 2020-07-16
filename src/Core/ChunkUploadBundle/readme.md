ChunkUploadBundle is an extension for SonataMediaBundle that allows you to chunk upload files using jQuery File Upload.

### Features

@todo

### Installation

1. Register the bundle in your `AppKernel.php`
    <pre>
    $bundles = array(

        //...

        new Core\ChunkUploadBundle\CoreChunkUploadBundle(),

    );
    </pre>
2. import the routing definition of the bundle in your `routing.yml`
3. Add the `coreChunkUploadBundle:Form:fields.html.twig` to your form resources:
    <pre>
    twig:
        form:
            resources:
                #...
                - 'coreChunkUploadBundle:Form:fields.html.twig'
    </pre>

4. Include the necessary assets in your template
    <pre>
    corechunkupload/css/jquery.fileupload.css

    corechunkupload/js/jquery.widget.js
    corechunkupload/js/jquery.iframe-transport.js
    corechunkupload/js/jquery.fileupload.js
    </pre>

5. Make sure your Media Entity implements `\Core\ChunkUploadBundle\Model\MediaInterface`. This will require to add the isTmp field to your schema.
6. Make sure that you have configured `core_chunk_upload`
    <pre>
    core_chunk_upload:
        media_class: Application\MediaBundle\Entity\Media #the fully qualified path to your Media entiy
    </pre>

### Usage

@todo

#### in case you would like to use it with media gallery admin, do the following:

1. follow the installation procedure described above
2. add the GalleryHasMediaSetterTrait to your gallery class
3. replace the galleryHasMedia field in the admin with the following:
    
    <pre>
    $formMapper->add('galleryHasMedias', 'core_chunk_upload_collection', array(
        'by_reference' => true,
    ))
    </pre>

4. add a dataTransformer to the galleryHasMedias field: "@core_chunk_upload.form_data_transformer.gallery_has_media_to_media_transformer"

