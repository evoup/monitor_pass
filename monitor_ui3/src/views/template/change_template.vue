<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="模板名">
        <el-col :span="8">
          <el-input v-model="form.name" placeholder="请输入模板名"/>
        </el-col>
      </el-form-item>
      <el-form-item label="所属服务器组">
        <el-select v-model="serverGroupSelectModel" multiple placeholder="请选择服务器组" style="width: 80%">
          <el-option
            v-for="item in serverGroupData"
            :key="item.id"
            :label="item.name"
            :value="item.id"/>
        </el-select>
      </el-form-item>
      <el-form-item label="关联的模板">
        <el-select v-model="templateSelectModel" multiple placeholder="请选择模板（可选）" style="width: 80%">
          <el-option
            v-for="item in templateData"
            :key="item.id"
            :label="item.name"
            :value="item.id"/>
        </el-select>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="changeTemplate(form.name, serverGroupSelectModel, templateSelectModel)">创建</el-button>
        <el-button @click="onCancel">取消</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { read_template, change_template, template_list } from '../../api/template'
import { server_group_list } from '../../api/server'

export default {
  data() {
    return {
      form: {
        name: '',
        type: [],
        resource: ''
      },
      // 列表前端分页
      pageList: {
        totalCount: '',
        pageSize: '',
        totalPage: '',
        currPage: ''
      },
      // 列表分页辅助类(传参)
      pageHelp: {
        page: 1, // 和后端参数一样
        size: 5, // 后端参数为size
        order: 'asc'
      },
      sortHelp: {
        prop: '',
        order: ''
      },
      listLoading: true,
      total: 0,
      serverGroupSelectModel: [],
      serverGroupData: [],
      templateSelectModel: [],
      templateData: []
    }
  },
  mounted() {
    this.fetchTemplateData({ 'id': this.$route.query.id })
    this.fetchServerGroupListData()
    this.fetchTemplateListData()
  },
  methods: {
    // 获取服务器组列表
    fetchServerGroupListData() {
      this.pageHelp.page = this.pageNum
      server_group_list().then(response => {
        this.serverGroupData = response.data.items
      })
    },
    // 获取所有模板列表
    fetchTemplateListData() {
      this.pageHelp.page = this.pageNum
      template_list().then(response => {
        this.templateData = response.data.items
      })
    },
    // 获取单个模板
    fetchTemplateData(id) {
      read_template(id).then(response => {
        this.form.name = response.data.item.name
      })
    },
    onCancel() {
      this.$message({
        message: 'cancel!',
        type: 'warning'
      })
    },
    changeTemplate(a, b, c) {
      change_template(a, b, c)
    }
  }
}
</script>

<style scoped>

</style>
