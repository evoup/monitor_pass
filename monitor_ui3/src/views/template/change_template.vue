<template>
  <div class="app-container">
    <el-row :gutter="20">
      <el-col
        :span="4"
      ><div class="grid-content el-form-item__label">
        监控项(<el-link
          type="primary"
          @click="jumpItemList(items)"
        >{{ items }}</el-link
        >)
      </div></el-col>
      <el-col
        :span="4"
      ><div class="grid-content el-form-item__label">
        触发器(<el-link
          type="primary"
          @click="jumpItemList(triggers)"
        >{{ triggers }}</el-link
        >)
      </div></el-col>
      <el-col :span="12"><div class="grid-content"/></el-col>
    </el-row>
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="模板名">
        <el-col :span="8">
          <el-input v-model="form.name" placeholder="请输入模板名" />
        </el-col>
      </el-form-item>
      <el-form-item label="所属服务器组">
        <el-select
          v-model="serverGroupSelectModel"
          multiple
          placeholder="请选择服务器组"
          style="width: 80%"
        >
          <el-option
            v-for="item in serverGroupData"
            :key="item.id"
            :label="item.name"
            :value="item.id"
          />
        </el-select>
      </el-form-item>
      <el-form-item label="关联的模板">
        <el-select
          v-model="templateSelectModel"
          multiple
          placeholder="请选择模板（可选）"
          style="width: 80%"
        >
          <el-option
            v-for="item in templateData"
            :key="item.id"
            :label="item.name"
            :aria-selected="true"
            :value="item.id"
          />
        </el-select>
      </el-form-item>
      <el-form-item>
        <el-button
          type="primary"
          @click="
            changeTemplate(
              form.name,
              serverGroupSelectModel,
              templateSelectModel
            )
          "
        >修改</el-button
        >
        <el-button @click="jumpTemplateList">取消</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import {
  read_template,
  change_template,
  template_list
} from '../../api/template'
import { server_group_list } from '../../api/server'

export default {
  data() {
    return {
      items: 0,
      triggers: 0,
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
        // 和后端参数一样
        page: 1,
        // 后端参数为size
        size: 5,
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
    this.fetchServerGroupListData()
    this.fetchTemplateListData()
    this.fetchTemplateData({ id: this.$route.query.id })
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
        this.items = response.data.item.items
        this.triggers = response.data.item.triggers
        this.form.name = response.data.item.name
        const serverGroupIds = response.data.item.server_group
        const a = []
        serverGroupIds.forEach(function(e) {
          a.push(e)
        })
        this.serverGroupSelectModel = a
        const templateIds = response.data.item.template_id
        const b = []
        templateIds.forEach(function(e) {
          b.push(e)
        })
        this.templateSelectModel = b
      })
    },
    changeTemplate(a, b, c) {
      change_template(this.$route.query.id, a, b, c)
    },
    jumpTemplateList() {
      this.$router.push({ path: '/template_list' })
    },
    jumpItemList(id) {
      this.$router.push({
        path: '/item_list',
        query: { template_id: this.$route.query.id }
      })
    }
  }
}
</script>

<style scoped>
:last-child {
  margin-bottom: 0;
}
.grid-content {
  border-radius: 4px;
  min-height: 36px;
}
</style>
