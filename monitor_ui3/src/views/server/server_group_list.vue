<template>
  <div class="app-container">
    <el-row type="flex" class="warp-breadcrum">
      <el-col :span="24">
        <el-col :span="3" :offset="21">
          <div class="grid-content">
            <el-button type="primary" @click="jumpAddServerGroup()"><i class="el-icon-plus el-icon--right" />添加服务器组</el-button>
          </div>
        </el-col>
      </el-col>
    </el-row>
    <el-table
      :v-loading="listLoading"
      :data="dataList"
      stripe
      border
      tooltip-effect="dark"
      style="width: 100%;margin-top:10px"
    >
      <el-table-column prop="id" label="序号" type="index" width="80" align="center" />
      <el-table-column
        label="服务器组"
        sortable="custom"
        prop="name"
        min-width="8%" />
      <el-table-column
        label="在线"
        sortable="custom"
        prop="on"
        min-width="8%" />
      <el-table-column
        label="宕机"
        sortable="custom"
        prop="down"
        min-width="8%" />
      <el-table-column
        label="状态未知"
        sortable="custom"
        prop="unknown"
        min-width="8%" />
      <el-table-column
        label="未监控"
        sortable="custom"
        prop="unmonitoring"
        min-width="8%" />
      <el-table-column
        label="正常事件数"
        sortable="custom"
        prop="date"
        min-width="9%" />
      <el-table-column
        label="一般事件数"
        sortable="custom"
        prop="date"
        min-width="9%" />
      <el-table-column
        label="严重事件数"
        sortable="custom"
        prop="date"
        min-width="9%" />
      <el-table-column min-width="12%">
        <template slot-scope="prop">
          <el-button size="small" type="primary" @click="lookUser(prop.$index,prop.row.u_uuid)">查看</el-button>
          <el-button size="small" type="danger" @click="deleteServerGroup(prop.row.id, prop.$index)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<!--suppress JSUnusedGlobalSymbols -->
<script>
import { server_group_list, delete_server_group } from '../../api/server'
export default {
  data() {
    return {
      typeData: [],
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
      filters: {
        name: '',
        type: 1
      },
      listLoading: true,
      total: 0,
      pageNum: 1
    }
  },
  created() {
    this.fetchData()
  },
  methods: {
    fetchData() {
      server_group_list(Object.assign(this.pageHelp, this.sortHelp)).then(response => {
        this.dataList = response.data.items
        this.pageList = response.data.page
        this.listLoading = false
        this.total = response.data.count
      })
    },
    // 跳转到服务器添加页面
    jumpAddServerGroup() {
      this.$router.push({ path: '/add_server_group' })
    },
    // 删除当前行
    deleteRow(index, rows) {
      rows.splice(index, 1)
    },
    // 删除服务器组
    deleteServerGroup(id, rowIdx) {
      delete_server_group({ id: id }).then(response => {
        this.deleteRow(rowIdx, this.dataList)
      })
    }
  }
}
</script>

<style scoped>

</style>
