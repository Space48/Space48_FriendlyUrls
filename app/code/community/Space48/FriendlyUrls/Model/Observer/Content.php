<?php
/**
 * Space48
 *
 * Space48 Search Engine Friendly Urls, which rewrites any catalog category urls into a SEO friendly format.
 *
 * @package Space48_FriendlyUrls
 */
/**
 * Space48 Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.space48.com/license.html
 *
 * @category   Space48
 * @package    Space48_Custom_Landing_Page
 * @version    0.1.1
 * @copyright  Copyright (c) 2013-2013 Space48 Ltd. (http://www.space48.com)
 * @license    http://www.space48.com/license.html
 * @company    Space48
 * @author     Steven Wan (steven.wan@space48.com)
 * @link       http://wiki.space48.com/modules/friendly_urls
 */

class Space48_FriendlyUrls_Model_Observer_Content
{
    /**
     * Update the category view meta tags and the canonical url
     *
     * @param Varien_Event_Observer $observer
     */
    public function _updateMetaData(Varien_Event_Observer $observer)
    {
       $routeName = Mage::app()->getRequest()->getRouteName();
       if (!(Mage::registry('brand'))) {
            if ($routeName != 'space48_salesection') {
	        	if ($routeName != 'space48_newproductssection') {
                    $this->_updateMetaContent(Mage::app()->getLayout());

                    // if 'Use Canonical Link Meta Tag For Categories' feature enabled
                    // format the canonical as required
                    if ($configValue = Mage::getStoreConfig('catalog/seo/category_canonical_tag')) {
                        $this->_updateCanonicalUrl();
                    }
		        }
            }
       } else {
           $this->_addCanonicalUrl();
       }
    }

    /**
     * Update the cms page canonical url
     *
     * @param Varien_Event_Observer $observer
     */
    public function _updateCmsPageCanonical(Varien_Event_Observer $observer)
    {
        $homePageName = Mage::getStoreConfig('web/default/cms_home_page');
        $cmsPageName = str_replace('/', '', Mage::app()->getRequest()->getRequestUri());
        $headBlock = Mage::app()->getLayout()->getBlock('head');
        $urlString = Mage::helper('core/url')->getCurrentUrl();

        if ($homePageName == $cmsPageName) {
            // if home page, use base url
            $url = Mage::getSingleton('core/url')->parseUrl($urlString);
            $headBlock->addLinkRel('canonical', $url->getBaseUrl());
        } else {
            $headBlock->addLinkRel('canonical', $urlString);
        }
    }

    protected function _isSalesSectionEnabled()
    {
        $rewrite = Mage::getModel('core/url_rewrite');
        $collection = $rewrite->getCollection();
        $collection->addFieldToFilter('id_path','space48_sale_section');
        if (!empty($collection)) {
          return true;
        }
        return false;
    }

    protected function _updateMetaContent($layout)
    {
        $appliedFilters = Mage::getSingleton('catalog/layer')->getState()->getFilters();
        $this->manufacturerAttribute = '';
        $attributesValues = array();
        if ($appliedFilters) {
            foreach ($appliedFilters as $item) {
                $attributeCode = $item->getFilter()->getRequestVar();
                if ($attributeCode == 'manufacturer') {
                    $optionValue = $item->getValue();
                    $productModel = Mage::getModel('catalog/product');
                    $attributes = $productModel->getResource()->getAttribute($attributeCode);
                    if ($attributes->usesSource()) {
                        $this->manufacturerAttribute = $attributes->getSource()->getOptionText($optionValue);
                    }
                } else if ($attributeCode == 'price') {
                    $layout->getBlock("head")->setRobots("NOINDEX,FOLLOW");
                } else {
                    $optionValue = $item->getValue();
                    $productModel = Mage::getModel('catalog/product');
                    $attributes = $productModel->getResource()->getAttribute($attributeCode);
                    if ($attributes->usesSource()) {
                        $attributesValues[] = $attributes->getSource()->getOptionText($optionValue);
                    }
                }
            }
        }

        $this->currentCategory = Mage::registry('current_category');
        $this->appliedFilters = Mage::registry('current_category');
        if (count($this->appliedFilters)) {
            $this->currentCategoryName = $this->currentCategory->getName();
            $this->headerTitle = trim($this->manufacturerAttribute ." ". $this->currentCategoryName);

            $this->titleAttributes = '';
            if ($attributesValues) {
                foreach ($attributesValues as $attributeValue) {
                    $this->headerTitle .= ' - '. $attributeValue;
                    $this->titleAttributes .= ' ' . $attributeValue;
                }
            }

            $this->_setCategoryMetaTitleData($layout);
        }
    }

    /**
     * Set Category/Meta titles and descriptions
     *
     * @param $layout layout object
     */
    protected function _setCategoryMetaTitleData($layout)
    {
        $_metaData = $this->_getCategoryMetaTitleOverrides();
        $_categoryTitle = $_metaData['category_name'] ? $_metaData['category_name'] : Mage::helper('space48friendlyurls')->__($this->headerTitle);
        $this->currentCategory->setName($_categoryTitle);

        $_categoryDescription = $_metaData['category_description'] ? $_metaData['category_description'] : Mage::helper('space48friendlyurls')->__('Below you can compare and buy: %s', $this->headerTitle);
        $this->currentCategory->setDescription($_categoryDescription);

        if (!$_headTitle = $_metaData['head_title']) {
            $_headTitle = str_replace('-', '', trim($this->manufacturerAttribute ." ". $this->currentCategoryName . $this->titleAttributes));
            $_headTitle = Mage::helper('space48friendlyurls')->__($_headTitle);
        }

        if (!$_headDescription = $_metaData['head_description']) {
            $_headDescription = trim('Find a wide range of '.$_headTitle.' at '.Mage::app()->getStore()->getName().'.');
        }

        $layout->getBlock("head")->setTitle($_headTitle);
        $layout->getBlock("head")->setDescription($_headDescription);
    }

    /**
     * Retrieve category title and description overrides
     *
     * @return array
     */
    protected function _getCategoryMetaTitleOverrides()
    {
        $_currentUrl = Mage::helper('core/url')->getCurrentUrl();
        $_currentUrl = parse_url($_currentUrl, PHP_URL_PATH);
        #$_currentUrl = "/pa-dj/dj-equipment/pioneer";
        $_meta = array();
        switch ($_currentUrl)
        {
            case "/pa-dj/dj-equipment/pioneer" :
                $_meta['head_title'] = "Pioneer DJ Equipment";
                $_meta['category_name'] = "Pioneer DJ Equipment";
                $_meta['head_description'] = "Buy Pioneer DJ Equipment - from Beginner to Pro - at Dawsons Music. Free Delivery on online orders over £50.";
                $_meta['category_description'] = "Pioneer, has been very influential in the development of home electronics, but  Pioneer DJ equipment has been revolutionary, establishing the brand as a world  leader. Browse below for the latest range of Pro and beginner Pioneer DJ gear including CDJ's, mixers, controllers and headphones.";
                break;

            /*case "/pa-dj/dj-equipment/mixers/pioneer" :
                $headBlock->setTitle("Pioneer DJ Mixers");
                $_category->setName("Pioneer DJ Mixers");
                $headBlock->setDescription("Buy Pioneer Mixers at Dawsons Music. Free UK Delivery on online orders over £50.");
                $_category->setDescription("Pioneer DJ mixers are a familiar sight in pro DJ environments, packing an astonishing amount of innovative technology into their design. The brand was amongst the first manufacturers to integrate professional effects processors into its mixers, and in its current range, some models offer DJ controller functionality, built-in audio interfaces and even the ability to network with DJ players and share a single song library.");
                break;

            case "/pa-dj/dj-equipment/turntables-and-cdj/brand-numark" :
                $headBlock->setTitle("Numark Turntables, Decks & CDJ");
                $_category->setName("Numark Turntables, Decks & CDJ");
                $headBlock->setDescription("Buy Numark Turntables, Decks & CDJ at Dawsons Music. Free UK Delivery on online orders over £50.");
                $_category->setDescription("Numark turntables grace innumerable DJ booths and the bedrooms of aspiring beginners globally. Though its reputation was established back in the days when vinyl was the media of choice, Numark decks now range from the ‘traditional’ vinyl players, through to digital media decks capable of playing from a variety of digital sources, an even being used as a DJ controller.");
                break;

            case "/drums-percussion/brand-alesis" :
                $headBlock->setTitle("Alesis Electronic Drums & Drum Kits");
                $_category->setName("Alesis Electronic Drums");
                $headBlock->setDescription("Buy Alesis Drums & Electronic Drum Kits at Dawsons Music. Free UK Delivery on orders over £50.");
                $_category->setDescription("Alesis drums have a history that can be traced back to ‘80s, and the period following the brand’s formation. These days, the Alesis drum range encompasses a selection of electronic drum kits, with several very affordable beginners’ kits that represent exceptional value.");
                break;

            case "/drums-percussion/brand-mapex" :
                $headBlock->setTitle("Mapex Drums");
                $_category->setName("Mapex Drums & Drum Accessories");
                $headBlock->setDescription("Buy Mapex Drums & Drum Kits at Dawsons Music. Free UK Delivery on orders over £50.");
                $_category->setDescription("The Mapex drum brand has managed to establish itself as one of the most popular and respected acoustic drum and drum hardware brands in a very short period.  Played by some of the most well known, and highly respected drummers in the world, its range encompasses everything from affordable beginners’ kits to full professional outfits.");
                break;

            case "/drums-percussion/digital-drum-kits/brand-roland" :
                $headBlock->setTitle("Roland Electronic Drums & Drums Kits");
                $_category->setName("Roland Electronic Drums & Drum Kits");
                $headBlock->setDescription("Buy Roland Electronic Drums, including the popular V Drums range at Dawsons.  Free UK Delivery on orders over £50.");
                $_category->setDescription("It is fair to say that Roland drums have transformed electronic drums from a curiosity to the most popular and practical means of learning how to play. Whether professional or beginner, there is a Roland drum kit that will fit your needs.");
                break;

            case "/drums-percussion/brand-yamaha" :
                $headBlock->setTitle("Yamaha Electronic Drums & Drum Machines");
                $_category->setName("Yamaha Drums & Drum Kits");
                $headBlock->setDescription("Buy Yamaha Drums & Drum Kits at Dawsons Music. Free UK Delivery on orders over £50.");
                $_category->setDescription("Yamaha has long held a reputation for great build quality and performance, and its electronic drums are no different. With a range that has a model for every level of player - from beginner to pro - this is a comprehensive selection, to say the least.  Browse the latest Yamaha Drum Kits and Drum Machines below.");
                break;

            case "/keyboards-pianos/brand-kawai" :
                $headBlock->setTitle("Kawai Pianos & Keyboards");
                $_category->setName("Kawai Pianos");
                $headBlock->setDescription("Buy Kawai pianos from our huge selection at Dawsons Music. Free UK Delivery on orders over £50.");
                $_category->setDescription("Pianos have been made under the Kawai name since 1927, giving them years of experience in the skills required to make a fine instrument. A wide range of Kawai pianos are available from Dawsons and available to try in many of our UK stores.");
                break;

            case "/keyboards-pianos/digital-pianos/brand-kawai" :
                $headBlock->setTitle("Kawai Digital Pianos");
                $_category->setName("Kawai Digital Pianos");
                $headBlock->setDescription("Buy Kawai digital pianos from our huge selection at Dawsons Music. Free UK Delivery on orders over £50.");
                $_category->setDescription("In recent years much focus has been placed on the Kawai digital piano range, bringing them to the forefront of many players minds when they pick a new instrument. The digital pianos that they provide come in a variety of styles to ensure everyone is catered for. ");
                break;

            case "/keyboards-pianos/stage-pianos/brand-korg" :
                $headBlock->setTitle("Korg Stage Pianos");
                $_category->setName("Korg Stage Pianos");
                $headBlock->setDescription("Buy Korg stage pianos from our huge selection at Dawsons Music. Free UK Delivery on orders over £50.");
                $_category->setDescription("With such a rich history, a Korg digital piano is the natural choice for many musicians. Although not known for them initially, the company started manufacturing them in the early ‘80s and haven’t looked back since, continually producing a healthy line-up of stage pianos to please many players.");
                break;

            case "/keyboards-pianos/synthesizers/brand-korg" :
                $headBlock->setTitle("Korg Synthesizers");
                $_category->setName("Korg Synthesizers");
                $headBlock->setDescription("Buy Korg synthesizers from our huge selection at Dawsons Music. Free UK Delivery on orders over £50.");
                $_category->setDescription("Korg has been making synthesisers for decades and have always been at the cutting edge of their creation. Many legendary Korg Synths are seen in the best studios in the world, but the sound and unique edge they provide is available in many packages.");
                break;

            case "/keyboards-pianos/brand-nord" :
                $headBlock->setTitle("Nord Pianos");
                $_category->setName("Nord Pianos");
                $headBlock->setDescription("Buy Nord pianos from our huge selection at Dawsons Music. Free UK Delivery on orders over £50.");
                $_category->setDescription("In the world of keyed instruments, Nord pianos have long been regarded as one of the most reputable brands. They provide reliable construction and great, characteristic sound across all of their products.");
                break;

            case "/keyboards-pianos/stage-pianos/brand-nord" :
                $headBlock->setTitle("Nord Stage Pianos");
                $_category->setName("Nord Stage Pianos");
                $headBlock->setDescription("Buy Nord stage pianos from our huge selection at Dawsons Music. Free UK Delivery on orders over £50.");
                $_category->setDescription("A thing of beauty and seen in the hands of many top musicians, a Nord stage piano is instantly noticeable. The colour is probably the most noticed feature, but the high quality tones produced by these instruments are right up there too. Available in a range of sizes, all of these stage pianos are made with exquisite care.");
                break;

            case "/keyboards-pianos/brand-roland" :
                $headBlock->setTitle("Roland Pianos & Keyboards");
                $_category->setName("Roland Pianos & Keyboards");
                $headBlock->setDescription("Buy Roland keyboards & pianos from our huge selection at Dawsons Music. Free UK Delivery on orders over £50.");
                $_category->setDescription("A huge range of Roland pianos and keyboards are available from Dawsons, mirroring their expansive product selection. One of the biggest names in musical instruments for a long time now, the Roland piano range has something to suit everyone from beginners to trained professionals.");
                break;

            case "/keyboards-pianos/digital-pianos/brand-roland" :
                $headBlock->setTitle("Roland Digital Pianos");
                $_category->setName("Roland Digital Pianos");
                $headBlock->setDescription("Buy Roland digital pianos from our huge selection at Dawsons Music. Free UK Delivery on orders over £50.");
                $_category->setDescription("Using cutting-edge technology to create instruments that sound great, feel incredible and even have on-board teaching functions, Roland digital pianos are a top choice for the home. Styled to fit in your living environment and available in a range of finishes, these instruments could easily become part of your life.");
                break;

            case "/keyboards-pianos/stage-pianos/brand-roland" :
                $headBlock->setTitle("Roland Stage Pianos");
                $_category->setName("Roland Stage Pianos");
                $headBlock->setDescription("Buy Roland stage pianos from our huge selection at Dawsons Music. Free UK Delivery on orders over £50.");
                $_category->setDescription("If you’re looking for a great instrument, Roland stage pianos are a highly recommended choice. This is proven by the amount of pro players who use them for performances the world over. The Roland range includes a huge choice to cater for all players so we’re sure to have something to suit your playing and performance needs.");
                break;

            case "/keyboards-pianos/acoustic-pianos/brand-yamaha" :
                $headBlock->setTitle("Yamaha Upright & Grand Pianos");
                $_category->setName("Yamaha Upright & Grand Pianos");
                $headBlock->setDescription("Buy Yamaha acoustic pianos from our huge selection at Dawsons Music. Choose from upright and grand styles, available to purchase with 0% finance.");
                $_category->setDescription("It’s hard to find a brand better known for quality in the acoustic piano market than Yamaha. The history and reputation behind every Yamaha grand piano and upright piano is known worldwide, with as much care being taken today as always has been to make some of the finest instruments in the world.");
                break;

            case "/keyboards-pianos/brand-yamaha" :
                $headBlock->setTitle("Yamaha Pianos & Keyboards");
                $_category->setName("Yamaha Pianos & Keyboards");
                $headBlock->setDescription("Buy Yamaha pianos & keyboards from our huge selection at Dawsons Music. Free UK Delivery on orders over £50.");
                $_category->setDescription("Steeped in history, the Yamaha name fills many pianists with excitement and for good reason. The range of products available from the legendary manufacturer is huge, with Yamaha pianos having a great reputation in the UK and all over the world. Their digital line-up is a huge part of the current interest around them and Yamaha keyboards seem to increase in popularity continuously.");
                break;

            case "/keyboards-pianos/digital-pianos/brand-yamaha" :
                $headBlock->setTitle("Yamaha Digital Pianos");
                $_category->setName("Yamaha Digital Pianos");
                $headBlock->setDescription("Buy Yamaha digital pianos from our huge selection at Dawsons Music. Free UK Delivery on orders over £50.");
                $_category->setDescription("Available in a range of styles, the Yamaha digital piano range is broad and therefore has models that meet the needs of beginners, pros and everyone in-between. Yamaha are certainly an excellent brand for first keyboards but provide so much more within their products.");
                break;

            case "/keyboards-pianos/digital-pianos/clavinova" :
                $headBlock->setTitle("Yamaha Clavinova Pianos");
                $_category->setName("Yamaha Clavinova Pianos");
                $headBlock->setDescription("Buy Yamaha clavinovas from our huge selection at Dawsons Music. Free UK Delivery on orders over £50.");
                $_category->setDescription("With traditional acoustic-like looks and great sound provided by superior technology, Yamaha Clavinova pianos are the perfect choice for many pianists. Both CLP and CVP ranges are available from Dawsons, with a great range of models to choose from.");
                break;

            case "/keyboards-pianos/stage-pianos/brand-yamaha" :
                $headBlock->setTitle("Yamaha Stage Pianos");
                $_category->setName("Yamaha Stage Pianos");
                $headBlock->setDescription("Buy Yamaha stage pianos from our huge selection at Dawsons Music. Free UK Delivery on orders over £50.");
                $_category->setDescription("Perfect for performance, Yamaha stage pianos sound great and are made for portability. A full range with many options within it, this selection includes pianos with great feeling keys and cutting edge technology for excellent tone.");
                break;

            default:
                $_category->setName(trim($brand ." ".$_category->getName().implode(' - ',$title)));

                $_category->setDescription('Below you can compare and buy: '.trim($_category->getName()));

                $category_name = str_replace(' -', '',$_category->getName());
                //$headBlock->setTitle(trim(preg_replace('/('.$titlePrefix.')|('.$titleSuffix.')/','',$category_name)));
                // the code above is needed for another store to do something with the prefix/suffix
                // for some reason on dawsons they are empty?
                $headBlock->setTitle($category_name);
                $headBlock->setDescription(trim('Find a wide range of '.$category_name.' at Dawsons Music.'));*/
        }

        return $_meta;
    }

    protected function _updateCanonicalUrl()
    {
        /*
         * If we are in catalog category view, then use canonical url override...
         */
        $category = Mage::registry('current_category');
        /* only do this if the store isn't Admin (store id 0) */
        if ($category && $category->getStoreId() != 0) {
            if ($url = $this->_getCanonicalUrl()) {
                $headBlock = Mage::app()->getLayout()->getBlock('head');
                $headBlock->removeItem('link_rel', $category->getUrl());
                $headBlock->addLinkRel('canonical', $url);
            }
        }
    }

    /**
     * Add new canonical to non category pages such as custom landing/brand pages
     */
    protected function _addCanonicalUrl()
    {
        if ($url = $this->_getCanonicalUrl()) {
            $headBlock = Mage::app()->getLayout()->getBlock('head');
            $headBlock->addLinkRel('canonical', $url);
        }
    }

    /* return the canonical url for a catalog category page */
    protected function _getCanonicalUrl()
    {
        $url = Mage::helper('core/url')->getCurrentUrl();
        $parsedUrl = parse_url($url);

        /* we only want the scheme, host and path, and not the query keys (see parse_url function) */
        $canonicalUrl = '';
        foreach ($parsedUrl as $k => $urlSegment) {
            if ($k != 'query') {
                if ($urlSegment == 'http') {
                    $urlSegment .= "://";
                }
                $canonicalUrl .= $urlSegment;
            }
        }

        return $this->_removeFiltersFromCanonicalUrl($canonicalUrl);
    }

    /**
     * Remove non required selected filters from canonical URL
     *
     * @param $canonicalUrl full url including filters
     * @return string canonical with filters removed
     */
    public function _removeFiltersFromCanonicalUrl($canonicalUrl)
    {
        // Modify the canonical only when filters have been applied
        if ($appliedFilters = Mage::getSingleton('catalog/layer')->getState()->getFilters()) {

            // attributes that are to be maintained in the canonical
            $attrToKeepInCanonicalUrl = explode(',', Mage::getStoreConfig('space48friendlyurls/settings/attributes'));

            // remove non filter (url/category) values from url that we want to keep as standard
            $categoryUrl = Mage::registry('current_category')->getUrl();
            $canonicalUrl = str_replace($categoryUrl . "/", "", $canonicalUrl);

            if ($urlSegments = explode("/", $canonicalUrl)) {
                foreach ($urlSegments as $urlSegment) {
                    if ($urlSegment) {
                        $lookupModel = Mage::getModel('space48friendlyurls/attributes')->getCollection()
                            ->addFieldToFilter('friendly_url_attribute_name', $urlSegment);
                        if ($item = $lookupModel->getFirstItem()) {
                            if (!in_array($item->getAttributeName(), $attrToKeepInCanonicalUrl)) {
                                $canonicalUrl = str_replace($urlSegment . "/", '', $canonicalUrl);
                            }
                        }
                    }
                }

                return $categoryUrl . "/" . $canonicalUrl;
            }
        }

        return $canonicalUrl;
    }
}
