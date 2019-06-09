<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="模板名">
        <el-col :span="8">
          <el-input v-model="form.name" placeholder="请输入模板名"/>
        </el-col>
      </el-form-item>
      <el-form-item label="所属服务器组">
        <el-select v-model="serverGroupSelect" multiple placeholder="请选择服务器组" style="width: 80%">
          <el-option
            v-for="item in serverGroupData"
            :key="item.group_id"
            :label="item.group_name"
            :value="item.group_name"/>
        </el-select>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="addTemplate(form.name)">创建</el-button>
        <el-button @click="onCancel">取消</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { add_template } from '../../api/template'
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
      serverGroupSelect: [],
      serverGroupData: []
    }
  },
  mounted() {
    this.fetchServerGroupListData()
  },
  methods: {
    // -----------------获取用户权限列表 Start--------------------------------
    fetchServerGroupListData() {
      this.pageHelp.page = this.pageNum
      server_group_list().then(response => {
        this.serverGroupData = response.data.items
      })
    },
    onSubmit() {
      add_template('a')
      this.$message('submit!')
    },
    onCancel() {
      this.$message({
        message: 'cancel!',
        type: 'warning'
      })
    },
    addTemplate(a) {
      add_template(a)
    }
  }
}
</script>

<style scoped>

</style>
