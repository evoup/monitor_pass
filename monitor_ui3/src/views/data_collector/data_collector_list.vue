<template>
  <div class="app-container">
    <el-row type="flex" class="warp-breadcrum" >
      <el-col :span="24">
        <el-col :span="3" :offset="20">
          <div class="grid-content">
            <el-button type="primary" @click="jumpAddDataCollector()"><i class="el-icon-plus el-icon--right" />添加数据收集器</el-button>
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
        label="数据收集器"
        sortable="custom"
        prop="name"
        min-width="20%" />
      <el-table-column
        label="ip"
        sortable="custom"
        prop="ip"
        min-width="30%" />
      <el-table-column
        label="port"
        sortable="custom"
        prop="port"
        min-width="20%" />
      <el-table-column label="操作">
        <template slot-scope="prop">
          <el-button size="small" type="primary">编辑</el-button>
          <el-button size="small" type="danger" @click="deleteIdc(prop.row.id, prop.$index)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<!--suppress JSUnusedGlobalSymbols -->
<script>
import { data_collector_list, delete_data_collector } from '../../api/data_collector'
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
      data_collector_list(Object.assign(this.pageHelp, this.sortHelp)).then(response => {
        this.dataList = response.data.items
        this.pageList = response.data.page
        this.listLoading = false
        this.total = response.data.count
      })
    },
    // 跳转到数据收集器添加页面
    jumpAddDataCollector() {
      this.$router.push({ path: '/add_data_collector' })
    },
    // 删除当前行
    deleteRow(index, rows) {
      rows.splice(index, 1)
    },
    // 删除数据收集器
    deleteIdc(id, rowIdx) {
      console.log(id)
      delete_data_collector({ id: id }).then(response => {
        this.deleteRow(rowIdx, this.dataList)
      })
    }
  }
}
</script>

<style scoped>

</style>
