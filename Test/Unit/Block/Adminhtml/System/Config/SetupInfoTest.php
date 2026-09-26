<?php
/**
 * Copyright © DMLab. All rights reserved.
 */
declare(strict_types=1);

namespace DmLab\AdminScimOkta\Test\Unit\Block\Adminhtml\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;
use DmLab\AdminScimOkta\Block\Adminhtml\System\Config\SetupInfo;
use DmLab\AdminScimOkta\Model\Okta\ScimSetupInfo;
use PHPUnit\Framework\TestCase;

/**
 * The frontend model is a pure adapter: the config field renders exactly what
 * {@see ScimSetupInfo} produces. Instantiated without the heavy block
 * constructor (the delegation is all that matters here).
 */
class SetupInfoTest extends TestCase
{
    public function testElementHtmlDelegatesToTheSetupInfo(): void
    {
        $setupInfo = $this->createStub(ScimSetupInfo::class);
        $setupInfo->method('getHtml')->willReturn('<div>rendered</div>');

        $block = (new \ReflectionClass(SetupInfo::class))->newInstanceWithoutConstructor();
        $property = new \ReflectionProperty(SetupInfo::class, 'setupInfo');
        $property->setValue($block, $setupInfo);

        $method = new \ReflectionMethod(SetupInfo::class, '_getElementHtml');
        $html = $method->invoke($block, $this->createStub(AbstractElement::class));

        self::assertSame('<div>rendered</div>', $html);
    }
}
