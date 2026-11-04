/**
 * 登录与注册页
 *
 * @package     resources\js\views
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */
<template>
    <div class="page">
        <el-card class="card">
            <template #header>登录 Sunny SaaS</template>
            <el-tabs v-model="tab">
                <el-tab-pane label="登录" name="login">
                    <el-form label-position="top" @submit.prevent="submitLogin">
                        <el-form-item label="邮箱">
                            <el-input v-model="loginForm.email" autocomplete="username" />
                        </el-form-item>
                        <el-form-item label="密码">
                            <el-input v-model="loginForm.password" type="password" show-password autocomplete="current-password" />
                        </el-form-item>
                        <el-button type="primary" :loading="loading" @click="submitLogin">登录</el-button>
                    </el-form>
                </el-tab-pane>
                <el-tab-pane label="注册" name="register">
                    <el-form label-position="top" @submit.prevent="submitRegister">
                        <el-form-item label="姓名">
                            <el-input v-model="registerForm.name" />
                        </el-form-item>
                        <el-form-item label="邮箱">
                            <el-input v-model="registerForm.email" />
                        </el-form-item>
                        <el-form-item label="密码">
                            <el-input v-model="registerForm.password" type="password" show-password />
                        </el-form-item>
                        <el-form-item label="确认密码">
                            <el-input v-model="registerForm.password_confirmation" type="password" show-password />
                        </el-form-item>
                        <el-button type="primary" :loading="loading" @click="submitRegister">注册</el-button>
                    </el-form>
                </el-tab-pane>
            </el-tabs>
            <el-alert v-if="error" class="error" :title="error" type="error" show-icon :closable="false" />
        </el-card>
    </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const tab = ref('login');
const loading = ref(false);
const error = ref('');
const router = useRouter();
const auth = useAuthStore();
const loginForm = reactive({ email: '', password: '' });
const registerForm = reactive({ name: '', email: '', password: '', password_confirmation: '' });

/** 提交登录 */
async function submitLogin(): Promise<void> {
    loading.value = true;
    error.value = '';

    try {
        await auth.login(loginForm.email, loginForm.password);
        await router.push('/tenants');
    } catch (caught) {
        error.value = messageOf(caught);
    } finally {
        loading.value = false;
    }
}

/** 提交注册 */
async function submitRegister(): Promise<void> {
    loading.value = true;
    error.value = '';

    try {
        await auth.register(registerForm);
        await router.push('/tenants');
    } catch (caught) {
        error.value = messageOf(caught);
    } finally {
        loading.value = false;
    }
}

/** 从接口响应里取出错误提示 */
function messageOf(caught: unknown): string {
    if (typeof caught === 'object' && caught && 'response' in caught) {
        const response = (caught as { response?: { data?: { message?: string } } }).response;

        return response?.data?.message ?? '请求失败';
    }

    return '请求失败';
}
</script>

<style scoped>
.page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f5f7fa;
}

.card {
    width: 420px;
}

.error {
    margin-top: 16px;
}
</style>


