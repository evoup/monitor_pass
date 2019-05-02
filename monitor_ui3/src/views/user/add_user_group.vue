<template>
  <div class="app-container">
    <el-tabs type="border-card">
      <el-tab-pane label="基本信息">
        <el-form ref="form" :model="form" label-width="120px">
          <el-form-item label="组名：">
            <el-col :span="8">
              <el-input v-model="form.name" placeholder="请输入组名"/>
            </el-col>
          </el-form-item>
          <el-form-item label="备注：">
            <el-col :span="8">
              <el-input v-model="form.desc" placeholder="请输入备注" type="textarea" />
            </el-col>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="addUserGroup(form.name, form.desc, form.desc, form.desc)">创建</el-button>
            <el-button @click="jumpUserGroupList">取消</el-button>
          </el-form-item>
        </el-form>
      </el-tab-pane>
      <el-tab-pane label="权限">权限</el-tab-pane>
      <el-tab-pane label="成员">
        <el-table
          :v-loading="listLoading"
          :data="dataList"
          stripe
          border
          tooltip-effect="dark"
          style="width: 100%"
          @sort-change="sortChange">
          <el-table-column :index="indexMethod" prop="id" label="序号" type="index" width="80" align="center" />
          <el-table-column
            label="用户名"
            sortable="custom"
            prop="name"
            width="120" />
          <el-table-column
            label="描述"
            prop="desc"
            width="380" />
          <el-table-column label="是否用户组成员">
            <template slot-scope="scope">
              <el-switch
                v-model="scope.row.belong_group"
                active-value="1"
                inactive-value="0"
              />
            </template>
          </el-table-column>
        </el-table>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script>
import { add_user_group } from '@/api/user'
import { user_list } from '../../api/user'
export default {
  data() {
    return {
      options: [{
        value: '1',
        label: '所有告警'
      }, {
        value: '2',
        label: '严重告警'
      }, {
        value: '3',
        label: '普通告警'
      }, {
        value: '4',
        label: '不接收'
      }],
      optionValue: '1',
      form: {
        name: '',
        desc: '',
        priv: '',
        members: ''
      },
      dataList: [], // 列表数据
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
      filters: {
        name: '',
        type: 1
      },
      listLoading: true,
      total: 0
    }
  },
  // created() {
  //   const len = 4
  //   for (var i = 0; i < len; i++) {
  //     var item = { value1: '0' }
  //     this.dataModel.push(item)
  //   }
  //   console.log(this.dataModel[0].value1)
  // },
  mounted() {
    this.fetchData()
  },
  methods: {
    fetchData() {
      this.pageHelp.page = this.pageNum
      user_list(Object.assign(this.pageHelp, this.sortHelp)).then(response => {
        this.dataList = response.data.items
        this.pageList = response.data.page
        this.listLoading = false
        this.total = response.data.count
      })
    },
    indexMethod(index) {
      return (this.pageList.currPage - 1) * this.pageList.pageSize + index + 1
    },
    handleSizeChange(val) {
      this.pageList.pageSize = val
      this.pageHelp.size = this.pageList.pageSize
      this.pageHelp.page = this.pageList.currPage
      this.fetchData()
    },
    // 点击分页sort-change
    handleCurrentChange(val) {
      this.pageNum = val
      this.fetchData()
    },
    sortChange(column, prop, order) {
      this.sortHelp.order = column.order
      this.sortHelp.prop = column.prop
      this.fetchData()
    },
    addUserGroup(a, b, c, d) {
      add_user_group(a, b, c, d)
    },
    // 跳转到用户组列表页面
    jumpUserGroupList() {
      this.$router.push({ path: '/user_group_list' })
    }
  }
}
</script>

<style scoped>

</style>
