<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="操作名称">
        <el-col :span="8">
          <el-input v-model="form.name" placeholder="请输入操作名称"/>
        </el-col>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
  export default {
    name: 'addOperation'
  }
</script>

<style scoped>

</style>
