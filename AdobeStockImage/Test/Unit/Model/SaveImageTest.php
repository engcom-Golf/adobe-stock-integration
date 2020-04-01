<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeStockImage\Test\Unit\Model;

use Magento\AdobeStockAssetApi\Api\SaveAssetInterface;
use Magento\AdobeStockImage\Model\Extract\AdobeStockAsset as DocumentToAsset;
use Magento\AdobeStockImage\Model\Extract\Keywords as DocumentToKeywords;
use Magento\AdobeStockImage\Model\SaveImage;
use Magento\AdobeStockImage\Model\SaveImageFile;
use Magento\AdobeStockImage\Model\SetLicensedInMediaGalleryGrid;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\Search\Document;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaGalleryApi\Model\Asset\Command\SaveInterface;
use Magento\MediaGalleryApi\Model\Keyword\Command\SaveAssetKeywordsInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Save image model.
 */
class SaveImageTest extends TestCase
{
    /**
     * @var MockObject|SaveInterface
     */
    private $saveMediaAsset;

    /**
     * @var MockObject|SaveAssetInterface
     */
    private $saveAdobeStockAsset;

    /**
     * @var MockObject|DocumentToAsset
     */
    private $documentToAsset;

    /**
     * @var MockObject|DocumentToKeywords
     */
    private $documentToKeywords;

    /**
     * @var MockObject|SaveAssetKeywordsInterface
     */
    private $saveAssetKeywords;

    /**
     * @var SetLicensedInMediaGalleryGrid|MockObject
     */
    private $setLicensedInMediaGalleryGridMock;

    /**
     * @var SaveImageFile|MockObject
     */
    private $saveImageFileMock;

    /**
     * @var SaveImage
     */
    private $saveImage;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->saveAdobeStockAsset = $this->createMock(SaveAssetInterface::class);
        $this->documentToAsset = $this->createMock(DocumentToAsset::class);
        $this->documentToKeywords = $this->createMock(DocumentToKeywords::class);
        $this->saveAssetKeywords = $this->createMock(SaveAssetKeywordsInterface::class);
        $this->setLicensedInMediaGalleryGridMock = $this->createMock(SetLicensedInMediaGalleryGrid::class);
        $this->saveImageFileMock = $this->createMock(SaveImageFile::class);

        $this->saveImage = (new ObjectManager($this))->getObject(
            SaveImage::class,
            [
                'saveAdobeStockAsset' =>  $this->saveAdobeStockAsset,
                'documentToAsset' =>  $this->documentToAsset,
                'documentToKeywords' => $this->documentToKeywords,
                'saveAssetKeywords' => $this->saveAssetKeywords,
                'setLicensedInMediaGalleryGrid' => $this->setLicensedInMediaGalleryGridMock,
            ]
        );
    }

    /**
     * Verify that image can be saved.
     *
     * @param Document $document
     * @param bool $delete
     *
     * @dataProvider assetProvider
     */
    public function testExecute(Document $document, bool $delete): void
    {
        $mediaGalleryAssetId = 42;

        $this->saveMediaAsset->expects($this->once())
            ->method('execute')
            ->willReturn($mediaGalleryAssetId);

        $this->documentToKeywords->expects($this->once())
            ->method('convert')
            ->with($document);

        $this->saveAssetKeywords->expects($this->once())
            ->method('execute');

        $this->documentToAsset->expects($this->once())
            ->method('convert')
            ->with($document);

        $this->saveAdobeStockAsset->expects($this->once())
            ->method('execute');

        $this->setLicensedInMediaGalleryGridMock->expects($this->once())
            ->method('execute');

        $this->saveImage->execute($document, 'https://as2.ftcdn.net/jpg/500_FemVonDcttCeKiOXFk.jpg', 'path');
    }

    /**
     * Data provider for testExecute
     *
     * @return array
     */
    public function assetProvider(): array
    {
        return [
            [
                'document' => $this->getDocument(),
                'delete' => false
            ],
            [
                'document' => $this->getDocument('filepath.jpg'),
                'delete' => true
            ],
        ];
    }

    /**
     * Get document
     *
     * @param string|null $path
     * @return MockObject
     */
    private function getDocument(?string $path = null): MockObject
    {
        $document = $this->createMock(Document::class);
        $pathAttribute = $this->createMock(AttributeInterface::class);
        $pathAttribute->expects($this->once())
            ->method('getValue')
            ->willReturn($path);
        $document->expects($this->once())
            ->method('getCustomAttribute')
            ->with('path')
            ->willReturn($pathAttribute);

        return $document;
    }
}
