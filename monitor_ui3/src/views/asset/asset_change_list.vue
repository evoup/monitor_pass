<template>
  <div class="app-container">
    <el-table
      :v-loading="listLoading"
      :data="dataList"
      stripe
      border
      tooltip-effect="dark"
      style="width: 100%;margin-top:10px"
      @sort-change="sortChange"
    >
      <el-table-column prop="id" label="序号" type="index" width="80px" align="center" />
      <el-table-column
        label="内容"
        sortable="custom"
        prop="content"
        min-width="40%">
        <template slot-scope="prop">
          <span v-if="prop.row.type === 1">服务器</span>
          <span v-if="prop.row.type === 2">防火墙</span>
          <span v-if="prop.row.type === 3">网络设备</span>
          <b>{{ prop.row.name }}</b>
          : {{ prop.row.content }}
        </template>
      </el-table-column>
      <el-table-column
        label="创建"
        sortable="custom"
        prop="creator"
        min-width="10%" >
        <template slot-scope="prop">
          <span v-if="prop.row.creator === null" type="success">自动采集</span>
        </template>
      </el-table-column>
      <el-table-column
        label="创建日期"
        sortable="custom"
        prop="create_at"
        min-width="10%" />
    </el-table>
    <el-col :span="24" class="toolbar block">
      <el-pagination
        :page-sizes="[7,10,15]"
        :page-size="7"
        :total="total"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange" />
    </el-col>
  </div>
</template>

<script>
import { asset_record_list } from '../../api/asset'

export default {
  name: 'AssetRecordList',
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
      pageNum: 1
    }
  },
  created() {
    this.fetchData()
  },
  methods: {
    fetchData() {
      this.listLoading = true
      this.pageHelp.page = this.pageNum
      asset_record_list(Object.assign(this.pageHelp, this.sortHelp)).then(response => {
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
    }
  }
}
</script>

<style scoped>

</style>
