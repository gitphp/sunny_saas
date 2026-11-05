<!--
 * 我收到的抄送
 *
 * @package     resources\js\views\approval
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
-->
<template>
    <TaskListView title="我收到的" source="tasks" box="cc" />
</template>

<script setup lang="ts">
import TaskListView from '@/views/approval/TaskListView.vue';
</script>
