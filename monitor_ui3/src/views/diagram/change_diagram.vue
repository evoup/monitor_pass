<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="名称">
        <el-col :span="8">
          <el-input v-model="form.name" placeholder="请输入图表名称"/>
        </el-col>
      </el-form-item>
      <el-form-item label="宽度">
        <el-col :span="8">
          <el-input v-model="form.width" placeholder="请输入宽度"/>
        </el-col>
      </el-form-item>
      <el-form-item label="高度">
        <el-col :span="8">
          <el-input v-model="form.height" placeholder="请输入高度"/>
        </el-col>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
export default {
  name: 'ChangeDiagram',
  data() {
    return {
      form: {
        name: '',
        width: null,
        height: null
      }
    }
  }
}
</script>

<style scoped>

</style>
