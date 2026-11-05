<!--
 * 发起申请页
 *
 * @package     resources\js\views\approval
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
-->
<template>
    <div>
        <div class="toolbar">
            <h2>发起申请</h2>
            <el-input v-model="keyword" placeholder="搜索流程" clearable style="width: 240px" />
        </div>
        <section v-for="group in filtered" :key="group.id" class="group">
            <h3>{{ group.name }}</h3>
            <div class="cards">
                <button v-for="item in group.definitions" :key="item.id" class="card" type="button" @click="open(item)">
                    <strong>{{ item.name }}</strong>
                    <span>{{ item.description || '已发布流程' }}</span>
                </button>
            </div>
        </section>
        <el-empty v-if="filtered.length === 0" description="还没有已发布的流程" />
        <el-drawer v-model="visible" :title="current?.name || '发起申请'" size="460px">
            <template v-if="current">
                <el-form label-position="top">
                    <el-form-item label="标题"><el-input v-model="title" /></el-form-item>
                    <el-form-item v-for="field in current.form_schema.fields" :key="field.key" :label="field.label">
                        <el-input v-if="field.type === 'textarea'" v-model="form[field.key]" type="textarea" />
                        <el-input-number v-else-if="field.type === 'number' || field.type === 'money'" v-model="form[field.key]" style="width: 100%" />
                        <el-date-picker v-else-if="field.type === 'date'" v-model="form[field.key]" type="date" value-format="YYYY-MM-DD" />
                        <el-select v-else-if="field.type === 'select' || field.type === 'radio'" v-model="form[field.key]" style="width: 100%">
                            <el-option v-for="option in field.options" :key="option.value" :label="option.label" :value="option.value" />
                        </el-select>
                        <el-input v-else v-model="form[field.key]" />
                    </el-form-item>
                </el-form>
                <h3>流程</h3>
                <p class="flow">{{ flowText }}</p>
                <el-button v-if="access.can('approval.instances.store')" type="primary" :loading="saving" @click="submit">提交</el-button>
            </template>
        </el-drawer>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { fetchCatalog, type CatalogDefinition, type CatalogGroup } from '@/api/approval';
import { apiErrorMessage } from '@/api/error';
import { http } from '@/api/http';
import { useAccessStore } from '@/stores/access';

const access = useAccessStore();
const groups = ref<CatalogGroup[]>([]);
const keyword = ref('');
const visible = ref(false);
const saving = ref(false);
const current = ref<CatalogDefinition | null>(null);
const title = ref('');
const form = reactive<Record<string, string | number | undefined>>({});

const filtered = computed(() => groups.value
    .map((group) => ({
        ...group,
        definitions: group.definitions.filter((item) => item.name.includes(keyword.value.trim())),
    }))
    .filter((group) => group.definitions.length > 0));

const flowText = computed(() => current.value?.flow_schema.nodes.map((node) => node.name).join(' → ') || '');

onMounted(async () => {
    groups.value = await fetchCatalog();
});

/** 打开发起表单 */
function open(item: CatalogDefinition): void {
    current.value = item;
    title.value = item.name;
    Object.keys(form).forEach((key) => delete form[key]);
    visible.value = true;
}

/** 按当前发布版本提交 */
async function submit(): Promise<void> {
    if (!current.value) {
        return;
    }

    saving.value = true;

    try {
        await http.post('/api/v1/approval/instances', {
            definition_code: current.value.code,
            title: title.value,
            form_data: { ...form },
        });
        visible.value = false;
        ElMessage.success('已提交');
    } catch (caught) {
        ElMessage.error(apiErrorMessage(caught));
    } finally {
        saving.value = false;
    }
}
</script>

<style scoped>
.toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
h2, h3 { margin: 0; font-size: 18px; }
.group { margin-bottom: 20px; }
.cards { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 8px; }
.card { width: 180px; text-align: left; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; padding: 14px; cursor: pointer; }
.card span { display: block; color: #6b7280; font-size: 12px; margin-top: 6px; }
.flow { color: #374151; line-height: 1.6; }
</style>
