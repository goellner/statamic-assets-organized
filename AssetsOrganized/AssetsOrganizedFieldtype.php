<?php

namespace Statamic\Addons\AssetsOrganized;

use Statamic\Addons\Assets\AssetsFieldtype;
use Statamic\Extend\Fieldtype;
use Statamic\API\Helper;
use Statamic\API\Asset;
use Statamic\API\AssetContainer;
use Statamic\API\Folder;
use Statamic\API\File;

class AssetsOrganizedFieldtype extends AssetsFieldtype
{
    public $category = ['media', 'relationship'];

    private $asset_container_url;

    public function canHaveDefault()
    {
        return false;
    }

    public function blank()
    {
        return [];
    }

    public function preProcess($data)
    {

        $max_files = (int) $this->getFieldConfig('max_files');

        if ($max_files === 1 && empty($data)) {
            return $data;
        }

        return Helper::ensureArray($data);
    }

    public function process($data)
    {

        $initial_folder = $data['initialFolder'];
        $slug = $data['slug'];

        $this->asset_container_url = $this->getAssetContainer($data['asset_container']);

        $ret = [];
        if(is_array($data['assets'])) {
            foreach($data['assets'] as $single_asset) {
                $ret[] = $this->moveAndRenameAsset($single_asset, $initial_folder, $slug);
            }
        } else {
            $ret[] = $this->moveAndRenameAsset($data['assets'], $initial_folder, $slug);
        }

        $this->cleanUp($initial_folder);

        $max_files = (int) $this->getFieldConfig('max_files');

        if ($max_files === 1) {
            return array_get($ret, 0);
        }
        return $ret;

    }

    private function moveAndRenameAsset($single_asset, $initial_folder, $slug) {

        $exploded_path = explode('/', $single_asset);
        $file_name = array_pop($exploded_path);

        $new_path = $this->asset_container_url . '/' . $initial_folder . '/' . $slug . '/' . $file_name;

        $full_file_path = $this->asset_container_url . '/' . $initial_folder . '/' . $slug . '/' . $file_name;
        $move_path = $initial_folder . '/' . $slug . '/';

        $asset_factory = Asset::find($single_asset);

        if(!File::exists($full_file_path)) {
            $asset_factory->move($move_path);
        } else {
            return $single_asset;
        }

        return $new_path;

    }

    private function getAssetContainer($asset_container) {
        $container = AssetContainer::find($asset_container);
        $ret = $container->url();
        return $ret;
    }

    private function cleanUp($initial_folder) {
        Folder::deleteEmptySubfolders($this->asset_container_url . '/' . $initial_folder . '/' );
    }
}
