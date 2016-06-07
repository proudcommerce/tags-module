<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */


/**
 * @inheritdoc
 */
class oetagsArticleTagListEE extends oetagsArticleTagList
{

    /**
     * @inheritdoc
     */
    public function executeDependencyEvent()
    {
        parent::executeDependencyEvent();

        // proxy cache dependencies
        $cache = \oxRegistry::get('oxReverseProxyBackend');

        if ($cache->isEnabled()) {
            $proxyCacheUrls = oxNew('oxReverseProxyUrlGenerator');
            //widgets
            $proxyCacheUrls->setWidget('oetagstagcloudwidget');
            $seoEncoderTag = \oxRegistry::get("oetagsSeoEncoderTag");

            $tags = $this->get();
            foreach ($tags as $tag) {
                $proxyCacheUrls->setUrl($seoEncoderTag->getTagUri($tag));
            }
            $cache->set($proxyCacheUrls->getUrls());
        }
    }

}
