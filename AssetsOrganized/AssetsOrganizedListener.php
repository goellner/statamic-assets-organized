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


    public $events = ['content.saved' => 'moveAssets'];

    private $asset_container;

    public function moveAssets($content, $original) {
        if($content instanceof Entry) {

            $changed = false;
            $new_images = [];

            $this->asset_container = AssetContainer::all()->map(function ($container) {
                return $container->url();
            })->toArray();

            $data = $content->data();

            if($this->processAssets($data, $content, $original)) {
                $content->data($data);
                $content->save();
            }

        }
    }

    private function processAssets(&$data, $content, $original) {

        $ret = false;

        foreach($this->asset_container as $single_asset_container) {
            $single_asset_container_trimmed = ltrim($single_asset_container, '/');
            if(is_array($data)) {
                foreach($data as $key => &$value) {
                    if(!is_array($value)) {
                        if(strpos($value, $single_asset_container_trimmed) || strpos($value, $single_asset_container_trimmed) === 0) {
                            $ret = $this->processSingleAsset($value, $content, $original, $single_asset_container_trimmed) || $ret;
                        }
                    }
                    else {
                        if(in_array($single_asset_container_trimmed, $value)) {
                            $ret = false;
                            foreach($value as &$asset) {
                                $ret = $this->processSingleAsset($asset, $content, $original, $single_asset_container_trimmed) || $ret;
                            }
                        }
                        else {
                            $ret = $this->processAssets($value, $content, $original) || $ret;
                        }
                    }
                }
            }

        }

        return $ret;
    }

    private function processSingleAsset(&$asset, $content, $original, $single_asset_container_trimmed) {

        $changed = true;
        $asset_factory = Asset::find($asset);

        $matches = array();
        preg_match('/^[^\/]*/' , $asset_factory->path(), $matches);

        $new_path = $matches[0] . '/' . $content->slug() . '/';

        if($this->startsWith($asset_factory->path(), $new_path)) {
            return false;
        }
        else {
            $asset_factory->move($new_path);

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
