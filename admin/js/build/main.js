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

    if( typeof updatedVal === 'undefined' ) {
        return;
    }

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

    if( typeof updatedVal === 'undefined' ) {
        return;
    }

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
        imgback = attributeByName('imgback'),
        vidtype = attributeByName('vidtype'),
        youtube = attributeByName('youtube'),
        vimeo = attributeByName('vimeo'),
        vidsource = attributeByName('vidsource'),
        autoplay = attributeByName('autoplay'),
        fallbackimg = attributeByName('fallbackimg'),
        mediaposition = attributeByName('mediaposition'),
        textposition = attributeByName('textposition'),
        imgoverlay = attributeByName('imgoverlay'),
        textwidth = attributeByName('textwidth');

    if( typeof updatedVal === 'undefined' ) {
        return;
    }

    switch (updatedVal) {
        case 'basic':
            bgcolor.$el.show();
            textbgcolor.$el.hide();
            img.$el.hide();
            imgcaption.$el.hide();
            imgback.$el.hide();
            // vidtype.$el.hide();
            // youtube.$el.hide();
            // vimeo.$el.hide();
            // vidsource.$el.hide();
            // autoplay.$el.hide();
            // fallbackimg.$el.hide();
            mediaposition.$el.hide();
            textposition.$el.hide();
            imgoverlay.$el.hide();
            textwidth.$el.hide();
            break;

        case 'img':
            bgcolor.$el.show();
            textbgcolor.$el.hide();
            img.$el.show();
            imgcaption.$el.show();
            imgback.$el.show();
            // vidtype.$el.hide();
            // youtube.$el.hide();
            // vimeo.$el.hide();
            // vidsource.$el.hide();
            // autoplay.$el.hide();
            // fallbackimg.$el.hide();
            mediaposition.$el.show();
            textposition.$el.hide();
            imgoverlay.$el.hide();
            textwidth.$el.hide();
            break;

        case 'vid':
            bgcolor.$el.show();
            textbgcolor.$el.hide();
            img.$el.hide();
            imgcaption.$el.hide();
            imgback.$el.hide();
            // vidtype.$el.show();
            // youtube.$el.hide();
            // vimeo.$el.hide();
            // vidsource.$el.hide();
            // autoplay.$el.hide();
            // fallbackimg.$el.hide();
            mediaposition.$el.show();
            textposition.$el.hide();
            imgoverlay.$el.hide();
            textwidth.$el.hide();
            break;

        case 'bgimg':
            textbgcolor.$el.show();
            bgcolor.$el.hide();
            img.$el.show();
            imgcaption.$el.show();
            // vidtype.$el.hide();
            // youtube.$el.hide();
            // vimeo.$el.hide();
            // vidsource.$el.hide();
            // autoplay.$el.hide();
            // fallbackimg.$el.hide();
            mediaposition.$el.hide();
            textposition.$el.show();
            imgoverlay.$el.show();
            textwidth.$el.show();
            break;

        case 'bgvid':
            textbgcolor.$el.show();
            bgcolor.$el.hide();
            img.$el.hide();
            imgcaption.$el.hide();
            imgback.$el.hide();
            // vidtype.$el.hide();
            // youtube.$el.hide();
            // vimeo.$el.hide();
            // vidsource.$el.show();
            // autoplay.$el.show();
            // fallbackimg.$el.show();
            mediaposition.$el.hide();
            textposition.$el.show();
            imgoverlay.$el.show();
            textwidth.$el.show();
            break;

        default:
            bgcolor.$el.hide();
            textbgcolor.$el.hide();
            img.$el.hide();
            imgcaption.$el.hide();
            imgback.$el.hide();
            // vidtype.$el.hide();
            // youtube.$el.hide();
            // vimeo.$el.hide();
            // vidsource.$el.hide();
            // autoplay.$el.hide();
            // fallbackimg.$el.hide();
            mediaposition.$el.hide();
            textposition.$el.hide();
            imgoverlay.$el.hide();
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

    if( typeof updatedVal === 'undefined' ) {
        return;
    }

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

    if( typeof updatedVal === 'undefined' ) {
        return;
    }

    if ('text'=== updatedVal) {
        textcolor.$el.show();
        bgcolor.$el.hide();
    } else if ('background' === updatedVal)  {
        textcolor.$el.hide();
        bgcolor.$el.show();
    } else {
        textcolor.$el.hide();
        bgcolor.$el.hide();
    }
}
wp.shortcake.hooks.addAction('enhanced-heading.coloroptions', updateColorOptionsListener);
function updateNewsOptionsListener(changed, collection, shortcode) {

    function attributeByName(name) {
        return _.find(
            collection,
            function (viewModel) {
                return name === viewModel.model.get('attr');
            }
        );
    }

    var updatedVal = changed.value,
        count = attributeByName('max');

    if (updatedVal == 'month') {
        count.$el.hide();
    } else if (updatedVal != 'month')  {
        count.$el.show();
    }
}
wp.shortcake.hooks.addAction('livewhale.view', updateNewsOptionsListener);
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

    if( typeof updatedVal === 'undefined' ) {
        return;
    }

    if (updatedVal) {
        taxonomy.$el.show();
        taxterm.$el.show();
    } else {
        taxonomy.$el.hide();
        taxterm.$el.hide();
    }
}
wp.shortcake.hooks.addAction('recent-posts.limit_tax', updateTaxonomyListener);
// function updateNewsOptionsListener(changed, collection, shortcode) {

//     function attributeByName(name) {
//         return _.find(
//             collection,
//             function (viewModel) {
//                 return name === viewModel.model.get('attr');
//             }
//         );
//     }

//     var updatedVal = changed.value,
//         category = attributeByName('site_category_slug'),
//         count = attributeByName('count');
//         offset = attributeByName('offset');
//         postid = attributeByName('postid');

//     if (updatedVal == 'full') {
//         postid.$el.show();
//         offset.$el.hide();
//         category.$el.hide();
//         count.$el.hide();
//     } else if (updatedVal != 'full')  {
//         postid.$el.hide();
//         offset.$el.show();
//         category.$el.show();
//         count.$el.show();
//     } else {
//         postid.$el.hide();
//         offset.$el.hide();
//         category.$el.hide();
//         count.$el.hide();
//     }
// }
// wp.shortcake.hooks.addAction('uamswp_news.output', updateNewsOptionsListener);