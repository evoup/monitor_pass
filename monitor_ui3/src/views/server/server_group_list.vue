<template>
  <div class="app-container">
    <el-row type="flex" class="warp-breadcrum" >
      <el-col :span="24">
        <el-col :span="3" :offset="21">
          <div class="grid-content">
            <el-button type="primary"><i class="el-icon-plus el-icon--right" />添加服务器组</el-button>
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
      @sort-change="sortChange">
      <el-table-column :index="indexMethod" prop="id" label="序号" type="index" width="80" align="center" />
      <el-table-column
        label="服务器组"
        sortable="custom"
        prop="name"
        width="180" />
      <el-table-column
        label="在线数"
        sortable="custom"
        prop="ip"
        width="130" />
      <el-table-column
        label="宕机数"
        sortable="custom"
        prop="data_collector"
        width="130" />
      <el-table-column
        label="正常事件数"
        sortable="custom"
        prop="date"
        width="130" />
      <el-table-column
        label="正常事件数"
        sortable="custom"
        prop="date"
        width="130" />
      <el-table-column
        label="正常事件数"
        sortable="custom"
        prop="date"
        width="130" />
      <el-table-column label="操作">
        <template scope="scope">
          <el-button size="small" type="primary" @click="lookUser(scope.$index,scope.row.u_uuid)">查看</el-button>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<script>
import { server_group_list } from '@/api/server'
export default {
  data() {
    return {
      typeData: [],
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
    }
  }
}
</script>

<style scoped>

</style>
