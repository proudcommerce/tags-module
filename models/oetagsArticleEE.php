<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */


/**
 * @inheritdoc
 */
class oetagsArticleEE extends oetagsArticleEE_parent
{

    /**
     * Execute cache dependencies for other widgets.
     */
    protected function _updateOtherWidgetsDependency()
    {
        parent::_updateOtherWidgetsDependency();

        $reverseProxyBackend = $this->_getReverseProxyBackend();

        if ($reverseProxyBackend->isEnabled()) {
            $reverseProxyUrl = oxNew('oxReverseProxyUrlGenerator');
            $reverseProxyUrl->setWidget('oetagstagcloudwidget');
            $reverseProxyBackend->set($reverseProxyUrl->getUrls());
        }
    }

}
