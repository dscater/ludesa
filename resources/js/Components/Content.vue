<script setup></script>
<template>
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header" v-if="$slots.header">
            <div class="container-fluid">
                <slot name="header"></slot>
            </div>
            <!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content" id="contentAdmin">
            <div class="container-fluid">
                <slot />
            </div>
        </div>
        <!-- /.content -->
    </div>
</template>
