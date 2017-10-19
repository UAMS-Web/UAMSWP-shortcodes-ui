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