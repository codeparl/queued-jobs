<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Tests\Unit;

use SchoolPalm\QueuedJobs\Context\QueueContext;
use SchoolPalm\QueuedJobs\Tests\TestCase;

/**
 * Test suite for the QueueContext value object.
 *
 * @covers \SchoolPalm\QueuedJobs\Context\QueueContext
 */
final class QueueContextTest extends TestCase
{
    /**
     * Test that a QueueContext can be created with all properties.
     *
     * @return void
     */
    public function test_can_create_context_with_all_properties(): void
    {
        $context = new QueueContext(
            tenantId: 'tenant_1',
            schoolId: 10,
            userId: 5,
            module: 'reports',
            metadata: ['key' => 'value'],
        );

        $this->assertSame('tenant_1', $context->getTenantId());
        $this->assertSame(10, $context->getSchoolId());
        $this->assertSame(5, $context->getUserId());
        $this->assertSame('reports', $context->getModule());
        $this->assertSame(['key' => 'value'], $context->getMetadata());
    }

    /**
     * Test that a QueueContext can be created with partial properties.
     *
     * @return void
     */
    public function test_can_create_context_with_partial_properties(): void
    {
        $context = new QueueContext(
            tenantId: 'tenant_1',
            module: 'reports',
        );

        $this->assertSame('tenant_1', $context->getTenantId());
        $this->assertNull($context->getSchoolId());
        $this->assertNull($context->getUserId());
        $this->assertSame('reports', $context->getModule());
        $this->assertSame([], $context->getMetadata());
    }

    /**
     * Test that QueueContext is immutable.
     *
     * @return void
     */
    public function test_context_is_immutable(): void
    {
        $context = new QueueContext(
            tenantId: 'tenant_1',
            schoolId: 10,
        );

        $newContext = $context->withUserId(5);

        $this->assertNull($context->getUserId());
        $this->assertSame(5, $newContext->getUserId());
        $this->assertNotSame($context, $newContext);
    }

    /**
     * Test the withTenantId modifier.
     *
     * @return void
     */
    public function test_with_tenant_id(): void
    {
        $context = new QueueContext();
        $newContext = $context->withTenantId('tenant_2');

        $this->assertNull($context->getTenantId());
        $this->assertSame('tenant_2', $newContext->getTenantId());
    }

    /**
     * Test the withSchoolId modifier.
     *
     * @return void
     */
    public function test_with_school_id(): void
    {
        $context = new QueueContext();
        $newContext = $context->withSchoolId(20);

        $this->assertNull($context->getSchoolId());
        $this->assertSame(20, $newContext->getSchoolId());
    }

    /**
     * Test the withUserId modifier.
     *
     * @return void
     */
    public function test_with_user_id(): void
    {
        $context = new QueueContext();
        $newContext = $context->withUserId(15);

        $this->assertNull($context->getUserId());
        $this->assertSame(15, $newContext->getUserId());
    }

    /**
     * Test the withModule modifier.
     *
     * @return void
     */
    public function test_with_module(): void
    {
        $context = new QueueContext();
        $newContext = $context->withModule('attendance');

        $this->assertNull($context->getModule());
        $this->assertSame('attendance', $newContext->getModule());
    }

    /**
     * Test the withMetadata modifier.
     *
     * @return void
     */
    public function test_with_metadata(): void
    {
        $context = new QueueContext();
        $newContext = $context->withMetadata(['locale' => 'en']);

        $this->assertEmpty($context->getMetadata());
        $this->assertSame(['locale' => 'en'], $newContext->getMetadata());
    }

    /**
     * Test that withMetadata merges with existing metadata.
     *
     * @return void
     */
    public function test_with_metadata_merges(): void
    {
        $context = new QueueContext(
            metadata: ['existing' => 'value'],
        );

        $newContext = $context->withMetadata(['new' => 'data']);

        $this->assertSame(
            ['existing' => 'value', 'new' => 'data'],
            $newContext->getMetadata(),
        );
    }

    /**
     * Test conversion to and from an array.
     *
     * @return void
     */
    public function test_to_array_and_from_array(): void
    {
        $original = new QueueContext(
            tenantId: 'tenant_1',
            schoolId: 10,
            userId: 5,
            module: 'reports',
            metadata: ['key' => 'value'],
        );

        $array = $original->toArray();
        $restored = QueueContext::fromArray($array);

        $this->assertEquals($original->getTenantId(), $restored->getTenantId());
        $this->assertEquals($original->getSchoolId(), $restored->getSchoolId());
        $this->assertEquals($original->getUserId(), $restored->getUserId());
        $this->assertEquals($original->getModule(), $restored->getModule());
        $this->assertEquals($original->getMetadata(), $restored->getMetadata());
    }

    /**
     * Test that isEmpty returns true for an empty context.
     *
     * @return void
     */
    public function test_is_empty_returns_true_for_empty_context(): void
    {
        $context = new QueueContext();

        $this->assertTrue($context->isEmpty());
    }

    /**
     * Test that isEmpty returns false for a non-empty context.
     *
     * @return void
     */
    public function test_is_empty_returns_false_for_non_empty_context(): void
    {
        $context = new QueueContext(tenantId: 'tenant_1');

        $this->assertFalse($context->isEmpty());
    }

    /**
     * Test fromArray handles snake_case and camelCase keys.
     *
     * @return void
     */
    public function test_from_array_handles_both_key_formats(): void
    {
        $snakeCase = QueueContext::fromArray([
            'tenant_id' => 't1',
            'school_id' => 1,
            'user_id' => 2,
            'module' => 'test',
        ]);

        $camelCase = QueueContext::fromArray([
            'tenantId' => 't1',
            'schoolId' => 1,
            'userId' => 2,
            'module' => 'test',
        ]);

        $this->assertEquals($snakeCase->getTenantId(), $camelCase->getTenantId());
        $this->assertEquals($snakeCase->getSchoolId(), $camelCase->getSchoolId());
        $this->assertEquals($snakeCase->getUserId(), $camelCase->getUserId());
    }

    /**
     * Test getMetadataValue returns the correct value.
     *
     * @return void
     */
    public function test_get_metadata_value(): void
    {
        $context = new QueueContext(
            metadata: ['key' => 'value', 'number' => 42],
        );

        $this->assertSame('value', $context->getMetadataValue('key'));
        $this->assertSame(42, $context->getMetadataValue('number'));
        $this->assertNull($context->getMetadataValue('non_existent'));
        $this->assertSame('default', $context->getMetadataValue('missing', 'default'));
    }

    /**
     * Test toJson produces valid JSON.
     *
     * @return void
     */
    public function test_to_json(): void
    {
        $context = new QueueContext(
            tenantId: 'tenant_1',
            userId: 5,
        );

        $json = $context->toJson();
        $decoded = json_decode($json, true);

        $this->assertIsString($json);
        $this->assertSame('tenant_1', $decoded['tenant_id']);
        $this->assertSame(5, $decoded['user_id']);
    }
}

