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