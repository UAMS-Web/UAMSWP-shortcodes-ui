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