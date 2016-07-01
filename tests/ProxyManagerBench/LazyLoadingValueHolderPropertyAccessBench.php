<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

declare(strict_types=1);

namespace ProxyManagerBench\Functional;

use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\Proxy\VirtualProxyInterface;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Benchmark that provides results for state access/initialization time for lazy loading value holder proxies
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @BeforeMethods({"setUp"})
 */
class LazyLoadingValueHolderPropertyAccessBench
{
    /**
     * @var EmptyClass|VirtualProxyInterface
     */
    private $emptyClassProxy;

    /**
     * @var EmptyClass|VirtualProxyInterface
     */
    private $initializedEmptyClassProxy;

    /**
     * @var ClassWithPublicProperties|VirtualProxyInterface
     */
    private $publicPropertiesProxy;

    /**
     * @var ClassWithPublicProperties|VirtualProxyInterface
     */
    private $initializedPublicPropertiesProxy;

    /**
     * @var ClassWithMixedProperties|VirtualProxyInterface
     */
    private $mixedPropertiesProxy;

    /**
     * @var ClassWithMixedProperties|VirtualProxyInterface
     */
    private $initializedMixedPropertiesProxy;

    public function setUp()
    {
        $this->emptyClassProxy          = $this->buildProxy(EmptyClass::class);
        $this->publicPropertiesProxy    = $this->buildProxy(ClassWithPublicProperties::class);
        $this->mixedPropertiesProxy     = $this->buildProxy(ClassWithMixedProperties::class);

        $this->initializedEmptyClassProxy          = $this->buildProxy(EmptyClass::class);
        $this->initializedPublicPropertiesProxy    = $this->buildProxy(ClassWithPublicProperties::class);
        $this->initializedMixedPropertiesProxy     = $this->buildProxy(ClassWithMixedProperties::class);

        $this->initializedEmptyClassProxy->initializeProxy();
        $this->initializedPublicPropertiesProxy->initializeProxy();
        $this->initializedMixedPropertiesProxy->initializeProxy();
    }

    public function benchEmptyClassInitialization()
    {
        $this->emptyClassProxy->initializeProxy();
    }

    public function benchInitializedEmptyClassInitialization()
    {
        $this->initializedEmptyClassProxy->initializeProxy();
    }

    public function benchObjectWithPublicPropertiesInitialization()
    {
        $this->publicPropertiesProxy->initializeProxy();
    }

    public function benchInitializedObjectWithPublicPropertiesInitialization()
    {
        $this->initializedPublicPropertiesProxy->initializeProxy();
    }

    public function benchObjectWithPublicPropertiesPropertyRead()
    {
        $this->publicPropertiesProxy->property0;
    }

    public function benchInitializedObjectWithPublicPropertiesPropertyRead()
    {
        $this->initializedPublicPropertiesProxy->property0;
    }

    public function benchObjectWithPublicPropertiesPropertyWrite()
    {
        $this->publicPropertiesProxy->property0 = 'foo';
    }

    public function benchInitializedObjectWithPublicPropertiesPropertyWrite()
    {
        $this->initializedPublicPropertiesProxy->property0 = 'foo';
    }

    public function benchObjectWithPublicPropertiesPropertyIsset()
    {
        /* @noinspection PhpExpressionResultUnusedInspection */
        /* @noinspection UnSafeIsSetOverArrayInspection */
        isset($this->publicPropertiesProxy->property0);
    }

    public function benchInitializedObjectWithPublicPropertiesPropertyIsset()
    {
        /* @noinspection PhpExpressionResultUnusedInspection */
        /* @noinspection UnSafeIsSetOverArrayInspection */
        isset($this->initializedPublicPropertiesProxy->property0);
    }

    public function benchObjectWithPublicPropertiesPropertyUnset()
    {
        unset($this->publicPropertiesProxy->property0);
    }

    public function benchInitializedObjectWithPublicPropertiesPropertyUnset()
    {
        unset($this->initializedPublicPropertiesProxy->property0);
    }

    public function benchObjectWithMixedPropertiesInitialization()
    {
        $this->mixedPropertiesProxy->initializeProxy();
    }

    public function benchInitializedObjectWithMixedPropertiesInitialization()
    {
        $this->initializedMixedPropertiesProxy->initializeProxy();
    }

    public function benchObjectWithMixedPropertiesPropertyRead()
    {
        $this->mixedPropertiesProxy->publicProperty0;
    }

    public function benchInitializedObjectWithMixedPropertiesPropertyRead()
    {
        $this->initializedMixedPropertiesProxy->publicProperty0;
    }

    public function benchObjectWithMixedPropertiesPropertyWrite()
    {
        $this->mixedPropertiesProxy->publicProperty0 = 'foo';
    }

    public function benchInitializedObjectWithMixedPropertiesPropertyWrite()
    {
        $this->initializedMixedPropertiesProxy->publicProperty0 = 'foo';
    }

    public function benchObjectWithMixedPropertiesPropertyIsset()
    {
        /* @noinspection PhpExpressionResultUnusedInspection */
        /* @noinspection UnSafeIsSetOverArrayInspection */
        isset($this->mixedPropertiesProxy->publicProperty0);
    }

    public function benchInitializedObjectWithMixedPropertiesPropertyIsset()
    {
        /* @noinspection PhpExpressionResultUnusedInspection */
        /* @noinspection UnSafeIsSetOverArrayInspection */
        isset($this->initializedMixedPropertiesProxy->publicProperty0);
    }

    public function benchObjectWithMixedPropertiesPropertyUnset()
    {
        unset($this->mixedPropertiesProxy->publicProperty0);
    }

    public function benchInitializedObjectWithMixedPropertiesPropertyUnset()
    {
        unset($this->initializedMixedPropertiesProxy->publicProperty0);
    }

    private function buildProxy(string $originalClass) : VirtualProxyInterface
    {
        return ($this->generateProxyClass($originalClass))::staticProxyConstructor(
            function (
                & $valueHolder,
                VirtualProxyInterface $proxy,
                string $method,
                $params,
                & $initializer
            ) use ($originalClass) : bool {
                $initializer = null;
                $valueHolder = new $originalClass();

                return true;
            }
        );
    }

    private function generateProxyClass(string $originalClassName) : string
    {
        $generatedClassName = __CLASS__ . '\\' . $originalClassName;

        if (class_exists($generatedClassName)) {
            return $generatedClassName;
        }

        $generatedClass     = new ClassGenerator($generatedClassName);

        (new LazyLoadingValueHolderGenerator())->generate(new ReflectionClass($originalClassName), $generatedClass);
        (new EvaluatingGeneratorStrategy())->generate($generatedClass);

        return $generatedClassName;
    }
}
