<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="操作名称：">
        <el-col :span="8">
          <el-input v-model="form.name" placeholder="请输入操作名称"/>
        </el-col>
      </el-form-item>
      <el-form-item label="通知主题：">
        <el-col :span="8">
          <el-input v-model="form.subject" placeholder="请输入通知主题"/>
        </el-col>
      </el-form-item>
      <el-form-item label="通知内容：">
        <el-col :span="8">
          <el-input
            v-model="form.message"
            class="note"
            placeholder="请输入通知内容"
            type="textarea"
          />
        </el-col>
      </el-form-item>
      <el-form-item
        label="请选择模板："
      >
        <el-col :span="8">
          <el-select
            v-model="templateSelectModel"
            placeholder="请选择模板（可选）"
            style="width: 80%"
          >
            <el-option
              v-for="item in templateListData"
              :key="item.id"
              :label="item.name"
              :aria-selected="true"
              :value="item.id"
            />
          </el-select>
        </el-col>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { template_list } from '../../api/template'

export default {
  name: 'AddOperation',
  data() {
    return {
      form: {
        name: '',
        subject: '',
        message: ''
      },
      templateListData: [],
      templateSelectModel: null
    }
  },
  created() {
    this.fetchTemplateListData()
  },
  methods: {
    // 获取所有模板列表
    fetchTemplateListData() {
      template_list({ page: 1, size: 99999, order: 'asc' }).then(response => {
        this.templateListData = response.data.items
      })
    }
  }
}
</script>

<style scoped>

</style>
