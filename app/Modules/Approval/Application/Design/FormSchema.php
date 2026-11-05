<?php

/**
 * 审批表单结构
 *
 * @package     App\Modules\Approval\Application\Design
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Approval\Application\Design;

use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;

/**
 * 审批表单结构
 *
 * 字段类型覆盖文本、数值、金额、日期和选项，供请假、报销等不同流程复用。
 */
final class FormSchema
{
    /** @var list<string> */
    private const TYPES = ['text', 'textarea', 'number', 'money', 'date', 'select', 'radio'];

    /**
     * 整理并校验表单定义
     *
     * @param  array<string, mixed>  $schema
     * @return array{fields: list<array<string, mixed>>}
     */
    public static function normalize(array $schema): array
    {
        $fields = $schema['fields'] ?? [];

        if (! is_array($fields)) {
            throw new BusinessException(ErrorCode::VALIDATION, '表单字段格式不正确', 422);
        }

        $normalized = [];
        $seen = [];

        foreach (array_values($fields) as $field) {
            if (! is_array($field)) {
                throw new BusinessException(ErrorCode::VALIDATION, '表单字段格式不正确', 422);
            }

            $key = (string) ($field['key'] ?? '');
            $type = (string) ($field['type'] ?? '');
            $label = trim((string) ($field['label'] ?? ''));

            if (! preg_match('/^[a-z][a-z0-9_]{0,31}$/', $key)) {
                throw new BusinessException(ErrorCode::VALIDATION, '表单字段编码只能使用小写字母、数字和下划线', 422);
            }

            if (isset($seen[$key])) {
                throw new BusinessException(ErrorCode::VALIDATION, '表单字段编码不能重复', 422);
            }

            if (! in_array($type, self::TYPES, true)) {
                throw new BusinessException(ErrorCode::VALIDATION, '不支持的表单字段类型', 422);
            }

            if ($label === '') {
                throw new BusinessException(ErrorCode::VALIDATION, '表单字段名称不能为空', 422);
            }

            $seen[$key] = true;
            $normalized[] = [
                'key' => $key,
                'type' => $type,
                'label' => $label,
                'required' => (bool) ($field['required'] ?? false),
                'options' => self::options($type, $field['options'] ?? []),
            ];
        }

        return ['fields' => $normalized];
    }

    /**
     * 按表单定义清洗提交数据，并保留定义之外的标量，方便其他模块附带业务编号
     *
     * @param  array{fields: list<array<string, mixed>>}  $schema
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function validateData(array $schema, array $data): array
    {
        $clean = [];
        $known = [];

        foreach ($schema['fields'] as $field) {
            $key = $field['key'];
            $known[$key] = true;
            $present = array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '';

            if (! $present) {
                if ($field['required']) {
                    throw new BusinessException(ErrorCode::VALIDATION, $field['label'].'不能为空', 422);
                }

                continue;
            }

            $clean[$key] = self::cast($field, $data[$key]);
        }

        foreach ($data as $key => $value) {
            if (isset($known[(string) $key])) {
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $clean[(string) $key] = $value;
            }
        }

        return $clean;
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private static function cast(array $field, mixed $value): mixed
    {
        return match ($field['type']) {
            'number' => self::number($field['label'], $value),
            'money' => number_format(self::number($field['label'], $value), 2, '.', ''),
            'date' => self::date($field['label'], $value),
            'select', 'radio' => self::optionValue($field, $value),
            default => mb_substr(trim((string) $value), 0, $field['type'] === 'textarea' ? 2000 : 255),
        };
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private static function optionValue(array $field, mixed $value): string
    {
        $text = trim((string) $value);
        $allowed = array_column($field['options'], 'value');

        if ($allowed !== [] && ! in_array($text, $allowed, true)) {
            throw new BusinessException(ErrorCode::VALIDATION, $field['label'].'的选项不正确', 422);
        }

        return $text;
    }

    private static function number(string $label, mixed $value): float
    {
        if (! is_numeric($value)) {
            throw new BusinessException(ErrorCode::VALIDATION, $label.'必须是数字', 422);
        }

        return (float) $value;
    }

    private static function date(string $label, mixed $value): string
    {
        $text = trim((string) $value);

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $text)) {
            throw new BusinessException(ErrorCode::VALIDATION, $label.'必须是日期', 422);
        }

        return $text;
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    private static function options(string $type, mixed $options): array
    {
        if (! in_array($type, ['select', 'radio'], true)) {
            return [];
        }

        if (! is_array($options)) {
            throw new BusinessException(ErrorCode::VALIDATION, '选项格式不正确', 422);
        }

        $rows = [];

        foreach ($options as $option) {
            if (! is_array($option)) {
                throw new BusinessException(ErrorCode::VALIDATION, '选项格式不正确', 422);
            }

            $label = trim((string) ($option['label'] ?? ''));
            $value = trim((string) ($option['value'] ?? ''));

            if ($label === '' || $value === '') {
                throw new BusinessException(ErrorCode::VALIDATION, '选项的名称和值不能为空', 422);
            }

            $rows[] = ['label' => $label, 'value' => $value];
        }

        return $rows;
    }
}
