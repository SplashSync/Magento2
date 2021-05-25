<?php


namespace Splash\Local\Helpers;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Catalog\Model\Product\Media\Config;
use Splash\Client\Splash;
use Splash\Models\Objects\ImagesTrait;

/**
 * Magento 2 Eav Images Helper
 */
class ImagesHelper
{
    use ImagesTrait;

    /**
     * @var string
     */
    private static $mediaDir;

    /**
     * @var Config
     */
    private static $mediaConfig;

    /**
     * Get Magento Media Full Path from Value
     *
     * @param string $mediaPath
     * @param string|null $mediaLabel
     *
     * @return null|array
     */
    public static function encode(string $mediaPath, string $mediaLabel = null): ?array
    {
        //====================================================================//
        // Safety Check - Ensure File Exists
        $fullPath = self::getFullPath($mediaPath);
        if (!is_file($fullPath)) {
            return null;
        }
        //====================================================================//
        // Encode Image Array
        return self::images()->encode(
            // Image Legend/Label
            $mediaLabel ?: basename($mediaPath),
            // Image File Filename
            basename($mediaPath),
            // Image Server Path (Without Filename)
            dirname($fullPath)."/",
            // Image Public Url
            self::getPublicUrl($mediaPath)
        ) ?: null;
    }

    /**
     * Get Magento Media Full Path from Value
     *
     * @param string $mediaPath
     *
     * @return string
     */
    public static function getFullPath(string $mediaPath): string
    {
        return self::getMediaDir()."/".self::getMediaConfig()->getMediaPath($mediaPath);
    }

    /**
     * Get Magento Media Public Url from Value
     *
     * @param string $mediaPath
     *
     * @return string
     */
    public static function getPublicUrl(string $mediaPath): string
    {
        return self::getMediaConfig()->getMediaUrl($mediaPath);
    }

    /**
     * Get Magento Default Media Dir
     *
     * @return string
     */
    private static function getMediaDir(): string
    {
        if (!isset(self::$mediaDir)) {
            /** @var DirectoryList $directoryList */
            $directoryList = MageHelper::getModel(DirectoryList::class);
            try {
                self::$mediaDir = $directoryList->getPath('media');
            } catch (FileSystemException $e) {
            }
        }

        return self::$mediaDir;
    }


    /**
     * Get Magento Default Media Dir
     *
     * @return Config
     */
    private static function getMediaConfig(): Config
    {
        if (!isset(self::$mediaConfig)) {
            /** @var Config $mediaConfig */
            $mediaConfig = MageHelper::getModel(Config::class);
            self::$mediaConfig = $mediaConfig;
        }

        return self::$mediaConfig;
    }

}
