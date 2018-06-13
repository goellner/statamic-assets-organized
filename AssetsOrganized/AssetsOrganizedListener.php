<?php

namespace Statamic\Addons\AssetsOrganized;

use Statamic\Extend\Listener;
use Statamic\API\Content;
use Statamic\API\Config;
use Statamic\Data\Entries\Entry;
use Statamic\API\Asset;
use Statamic\API\AssetContainer;
use Statamic\API\Folder;

class AssetsOrganizedListener extends Listener
{
    /**
     * The events to be listened for, and the methods to call.
     *
     * @var array
     */


    public $events = [
        'content.saved' => 'moveAssets',
        'cp.add_to_head' => 'outputAssetURLs'
    ];

    private $asset_container;

    public function moveAssets($content, $original) {
        if($content instanceof Entry) {

            $changed = false;
            $new_images = [];

            $this->asset_container = AssetContainer::all()->map(function ($container) {
                return $container->url();
            })->toArray();

            $data = $content->data();

            $processedMap = [];

            if($this->processAssets($data, $content, $original, $processedMap)) {
                $content->data($data);
                $content->save();
            }

        }
    }

    public function outputAssetURLs() {

        $all_asset_container = AssetContainer::all()->map(function ($container) {
            return $container->url();
        })->toArray();

        return '<script>AssetsOrganized = { containers: ' . json_encode( $all_asset_container ) . ' };</script>';
    }

    private function processAssets(&$data, $content, $original, &$processedMap) {


        $ret = false;

        foreach($this->asset_container as $single_asset_container) {
            $single_asset_container_trimmed = ltrim($single_asset_container, '/');
            if(is_array($data)) {
                foreach($data as $key => &$value) {
                    if(!is_array($value)) {
                        if(strpos($value, $single_asset_container_trimmed) || strpos($value, $single_asset_container_trimmed) === 0) {
                            $ret = $this->processSingleAsset($value, $content, $original, $single_asset_container_trimmed, $processedMap) || $ret;
                        }
                    }
                    else {
                        if(in_array($single_asset_container_trimmed, $value)) {
                            $ret = false;
                            foreach($value as &$asset) {
                                $ret = $this->processSingleAsset($asset, $content, $original, $single_asset_container_trimmed, $processedMap) || $ret;
                            }
                        }
                        else {
                            $ret = $this->processAssets($value, $content, $original, $processedMap) || $ret;
                        }
                    }
                }
            }

        }

        return $ret;
    }

    private function processSingleAsset(&$asset, $content, $original, $single_asset_container_trimmed, &$processedMap) {

        $changed = true;

        if(array_key_exists($asset, $processedMap)) {
            $asset = $processedMap[$asset];
            return true;
        }

        $asset_factory = Asset::find($asset);

        $matches = array();

        preg_match('/^[^\/]*/' , $asset_factory->path(), $matches);

        $new_path = $matches[0] . '/' . $content->slug() . '/';

        if($this->startsWith($asset_factory->path(), $new_path)) {
            return false;
        }
        else {
            $asset_factory->move($new_path);

            $processedMap[$asset] = $asset_factory->uri();
            $asset = $asset_factory->uri();

            // if slug or id is changed, empty subfolders will get deleted
            foreach($this->asset_container as $single_asset_container) {
                Folder::deleteEmptySubfolders($single_asset_container . '/' . $matches[0] . '/' );
            }

            return true;
        }

    }

    private function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }


}
