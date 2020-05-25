/**
 * Akeneo app
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'backbone',
        'pim/form',
        'oro/messenger',
        'oro/mediator',
        'pim/fetcher-registry',
        'pim/init',
        'pim/init-translator',
        'oro/init-layout',
        'pimuser/js/init-signin',
        'pim/page-title',
        'pim/date-context',
        'pim/user-context',
        'pim/template/app',
        'pim/template/common/flash',
        'jquery.select2.placeholder'
    ],
    function (
        $,
        _,
        Backbone,
        BaseForm,
        messenger,
        mediator,
        FetcherRegistry,
        init,
        initTranslator,
        initLayout,
        initSignin,
        pageTitle,
        DateContext,
        UserContext,
        template,
        flashTemplate
    ) {
        return BaseForm.extend({
            tagName: 'div',
            className: 'app',
            template: _.template(template),
            flashTemplate: _.template(flashTemplate),

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                initLayout();
                initSignin();

                return BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(mediator, 'pim-app:overlay:show', this.showOverlay);
                this.listenTo(mediator, 'pim-app:overlay:hide', this.hideOverlay);

                return $.when(
                        FetcherRegistry.initialize(),
                        DateContext.initialize(),
                        UserContext.initialize()
                    )
                    .then(initTranslator.fetch)
                    .then(function () {
                        messenger.showQueuedMessages();

                        init();

                        pageTitle.set('Akeneo PIM');

                        return BaseForm.prototype.configure.apply(this, arguments);
                    }.bind(this));
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({}));

                if (!Backbone.History.started) {
                    Backbone.history.start();
                }

                return BaseForm.prototype.render.apply(this, arguments);
            },

            showOverlay: function () {
                this.$('#page').addClass('AknDefault-page--withOverlay');
                this.$('.AknDefault-mainContent').addClass('AknDefault-mainContent--withoutScroll');
            },

            hideOverlay: function () {
                this.$('#page').removeClass('AknDefault-page--withOverlay');
                this.$('.AknDefault-mainContent').removeClass('AknDefault-mainContent--withoutScroll');
            }
        });
    });
