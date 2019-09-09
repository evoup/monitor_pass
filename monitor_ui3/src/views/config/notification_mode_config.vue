<template>
  <div class="app-container">
    <el-form ref="form">
      <el-col :span="24">
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
            label="名称"
            sortable="custom"
            prop="name"
            min-width="10%" />
          <el-table-column
            label="状态"
            prop="enable"
            min-width="10%">
            <template slot-scope="prop">
              <el-tag v-if="prop.row.enabled !== true" type="danger">禁用</el-tag>
              <el-tag v-if="prop.row.enabled === true" type="success">启用</el-tag>
            </template>
          </el-table-column>
          <el-table-column
            label="详情"
            prop="detail"
            min-width="20%" />
          <el-table-column label="操作" min-width="16%">
            <template slot-scope="prop">
              <el-button size="small" type="primary" @click="jumpServerDetail(prop.row.id)">查看</el-button>
              <el-button size="small" type="primary" @click="jumpServerDetail(prop.row.id)">编辑</el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-col>
  </el-form></div>
</template>

<!--suppress JSUnusedGlobalSymbols -->
<script>
import { notifaction_mode_list } from '../../api/notification_mode'
export default {
  filters: {
    statusFilter(status) {
      const statusMap = {
        '在线': 'success',
        '离线': 'gray'
      }
      return statusMap[status]
    }
  },
  data() {
    return {
      // 列表数据
      dataList: [],
      // 列表前端分页
      pageList: {
        totalCount: '',
        pageSize: '',
        totalPage: '',
        currPage: ''
      },
      // 列表分页辅助类(传参)
      pageHelp: {
        page: 1,
        size: 7,
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
      total: 0,
      pageNum: 1,
      serverGroupSelectModel: 0,
      serverGroups: []
    }
  },
  created() {
    this.fetchData()
  },
  methods: {
    fetchData() {
      this.listLoading = true
      notifaction_mode_list().then(response => {
        this.dataList = response.data.items
        this.listLoading = false
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
    // 跳转到服务器详情页面
    jumpServerDetail(id) {
      this.$router.push({
        path: '/server_detail',
        query: { id: id }
      })
    }
  }
}
</script>
