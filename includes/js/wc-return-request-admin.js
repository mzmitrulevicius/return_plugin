jQuery(document).ready(function ($) {
    $('#upload_image_button').click(function (e) {
        e.preventDefault();

        var imageFrame;
        if (imageFrame) {
            imageFrame.open();
            return;
        }

        imageFrame = wp.media({
            title: 'Select or Upload Media',
            button: {
                text: 'Use this media'
            },
            multiple: false
        });

        imageFrame.on('select', function () {
            var attachment = imageFrame.state().get('selection').first().toJSON();
            $('#thank_you_image').val(attachment.url);
        });

        imageFrame.open();
    });
});
