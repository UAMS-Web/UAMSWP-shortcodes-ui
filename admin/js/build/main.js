// TO DO: https://github.com/wp-shortcake/shortcake/wiki/Event-Attribute-Callbacks
// TO DO: Form validation, eg. can't have an mp4 video without a fallback image.

function updateHeadingListener(changed, collection, shortcode) {

    function attributeByName(name) {
        return _.find(
            collection,
            function (viewModel) {
                return name === viewModel.model.get('attr');
            }
        );
    }

    var updatedVal = changed.value,
        headingtype = attributeByName('headingtype'),
        headingicon = attributeByName('headingicon');

    if (updatedVal) {
        headingtype.$el.show();
        headingicon.$el.show();
    } else {
        headingtype.$el.hide();
        headingicon.$el.hide();
    }
}
wp.shortcake.hooks.addAction('callout.heading', updateHeadingListener);

function updateURLListener(changed, collection, shortcode) {

    function attributeByName(name) {
        return _.find(
            collection,
            function (viewModel) {
                return name === viewModel.model.get('attr');
            }
        );
    }

    var updatedVal = changed.value,
        target = attributeByName('target');

    if (updatedVal) {
        target.$el.show();
    } else {
        target.$el.hide();
    }
}
wp.shortcake.hooks.addAction('callout.url', updateURLListener);

function updateTypeListener(changed, collection, shortcode) {

    function attributeByName(name) {
        return _.find(
            collection,
            function (viewModel) {
                return name === viewModel.model.get('attr');
            }
        );
    }

    var updatedVal = changed.value,
        bgcolor = attributeByName('bgcolor'),
        textbgcolor = attributeByName('textbgcolor'),
        img = attributeByName('img'),
        imgcaption = attributeByName('imgcaption'),
        vidtype = attributeByName('vidtype'),
        youtube = attributeByName('youtube'),
        vimeo = attributeByName('vimeo'),
        vidsource = attributeByName('vidsource'),
        autoplay = attributeByName('autoplay'),
        fallbackimg = attributeByName('fallbackimg'),
        mediaposition = attributeByName('mediaposition'),
        textposition = attributeByName('textposition'),
        textwidth = attributeByName('textwidth');

    switch (updatedVal) {
        case 'basic':
            bgcolor.$el.show();
            textbgcolor.$el.hide();
            img.$el.hide();
            imgcaption.$el.hide();
            vidtype.$el.hide();
            youtube.$el.hide();
            vimeo.$el.hide();
            vidsource.$el.hide();
            autoplay.$el.hide();
            fallbackimg.$el.hide();
            mediaposition.$el.hide();
            textposition.$el.hide();
            textwidth.$el.hide();
            break;

        case 'img':
            bgcolor.$el.show();
            textbgcolor.$el.hide();
            img.$el.show();
            imgcaption.$el.show();
            vidtype.$el.hide();
            youtube.$el.hide();
            vimeo.$el.hide();
            vidsource.$el.hide();
            autoplay.$el.hide();
            fallbackimg.$el.hide();
            mediaposition.$el.show();
            textposition.$el.hide();
            textwidth.$el.hide();
            break;

        case 'vid':
            bgcolor.$el.show();
            textbgcolor.$el.hide();
            img.$el.hide();
            imgcaption.$el.hide();
            vidtype.$el.show();
            youtube.$el.hide();
            vimeo.$el.hide();
            vidsource.$el.hide();
            autoplay.$el.hide();
            fallbackimg.$el.hide();
            mediaposition.$el.show();
            textposition.$el.hide();
            textwidth.$el.hide();
            break;

        case 'bgimg':
            textbgcolor.$el.show();
            bgcolor.$el.hide();
            img.$el.show();
            imgcaption.$el.show();
            vidtype.$el.hide();
            youtube.$el.hide();
            vimeo.$el.hide();
            vidsource.$el.hide();
            autoplay.$el.hide();
            fallbackimg.$el.hide();
            mediaposition.$el.hide();
            textposition.$el.show();
            textwidth.$el.show();
            break;

        case 'bgvid':
            textbgcolor.$el.show();
            bgcolor.$el.hide();
            img.$el.hide();
            imgcaption.$el.hide();
            vidtype.$el.hide();
            youtube.$el.hide();
            vimeo.$el.hide();
            vidsource.$el.show();
            autoplay.$el.show();
            fallbackimg.$el.show();
            mediaposition.$el.hide();
            textposition.$el.show();
            textwidth.$el.show();
            break;

        default:
            bgcolor.$el.hide();
            textbgcolor.$el.hide();
            img.$el.hide();
            imgcaption.$el.hide();
            vidtype.$el.hide();
            youtube.$el.hide();
            vimeo.$el.hide();
            vidsource.$el.hide();
            autoplay.$el.hide();
            fallbackimg.$el.hide();
            mediaposition.$el.hide();
            textposition.$el.hide();
            textwidth.$el.hide();
            break;
    }

}
wp.shortcake.hooks.addAction('callout.type', updateTypeListener);


function updateVidTypeListener(changed, collection, shortcode) {

    function attributeByName(name) {
        return _.find(
            collection,
            function (viewModel) {
                return name === viewModel.model.get('attr');
            }
        );
    }

    var updatedVal = changed.value,
        youtube = attributeByName('youtube'),
        vimeo = attributeByName('vimeo'),
        vidsource = attributeByName('vidsource'),
        autoplay = attributeByName('autoplay'),
        fallbackimg = attributeByName('fallbackimg');

    switch (updatedVal) {
        case 'youtube':
            youtube.$el.show();
            vimeo.$el.hide();
            vidsource.$el.hide();
            autoplay.$el.hide();
            fallbackimg.$el.hide();
            break;

        case 'vimeo':
            youtube.$el.hide();
            vimeo.$el.show();
            vidsource.$el.hide();
            autoplay.$el.hide();
            fallbackimg.$el.hide();
            break;

        case 'other':
            youtube.$el.hide();
            vimeo.$el.hide();
            vidsource.$el.show();
            autoplay.$el.show();
            fallbackimg.$el.show();
            break;

        default:
            youtube.$el.hide();
            vimeo.$el.hide();
            vidsource.$el.hide();
            autoplay.$el.hide();
            fallbackimg.$el.hide();
            break;
    }
}
wp.shortcake.hooks.addAction('callout.vidtype', updateVidTypeListener);
function updateColorOptionsListener(changed, collection, shortcode) {

    function attributeByName(name) {
        return _.find(
            collection,
            function (viewModel) {
                return name === viewModel.model.get('attr');
            }
        );
    }

    var updatedVal = changed.value,
        textcolor = attributeByName('textcolor'),
        bgcolor = attributeByName('bgcolor');

    if (updatedVal == 'text') {
        textcolor.$el.show();
        bgcolor.$el.hide();
    } else if (updatedVal == 'background')  {
        textcolor.$el.hide();
        bgcolor.$el.show();
    } else {
        textcolor.$el.hide();
        bgcolor.$el.hide();
    }
}
wp.shortcake.hooks.addAction('enhanced-heading.coloroptions', updateColorOptionsListener);
function updateTaxonomyListener(changed, collection, shortcode) {

    function attributeByName(name) {
        return _.find(
            collection,
            function (viewModel) {
                return name === viewModel.model.get('attr');
            }
        );
    }

    var updatedVal = changed.value,
        taxonomy = attributeByName('taxonomy'),
        taxterm = attributeByName('tax_term');

    if (updatedVal) {
        taxonomy.$el.show();
        taxterm.$el.show();
    } else {
        taxonomy.$el.hide();
        taxterm.$el.hide();
    }
}
wp.shortcake.hooks.addAction('recent-posts.limit_tax', updateTaxonomyListener);