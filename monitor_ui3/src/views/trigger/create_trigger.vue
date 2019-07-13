<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="触发器名称">
        <el-col :span="8">
          <el-input v-model="form.name" placeholder="请输入触发器名称"/>
        </el-col>
      </el-form-item>
      <el-form-item label="表达式">
        <el-col :span="8">
          <el-input v-model="form.expression" placeholder="请输入表达式" type="textarea"/>
        </el-col>
      </el-form-item>
      <el-form-item label="描述">
        <el-col :span="8">
          <el-input v-model="form.desc" placeholder="请输入描述" type="textarea"/>
        </el-col>
      </el-form-item>
      <el-form-item label="严重等级">
        <el-col :span="8">
          <el-select v-model="optionValue" placeholder="请选择">
            <el-option
              v-for="(item, index) in options"
              :key="index"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-col>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
export default {
  name: 'CreateTrigger',
  data() {
    return {
      form: {
        name: '',
        expression: '',
        desc: '',
        level: 0
      },
      options: [
        {
          value: '1',
          label: '信息'
        },
        {
          value: '2',
          label: '警告'
        },
        {
          value: '3',
          label: '严重警告'
        },
        {
          value: '4',
          label: '灾难警告'
        }
      ],
      optionValue: '2'
    }
  }
}
</script>

<style scoped>

</style>
