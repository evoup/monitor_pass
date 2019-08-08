<template>
  <div class="app-container">
    <el-row type="flex" class="warp-breadcrum" >
      <el-col :span="24">
        <el-col :span="3" :offset="21">
          <div class="grid-content">
            <el-button type="primary" @click="jumpAddIdc()"><i class="el-icon-plus el-icon--right" />添加机房</el-button>
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
        label="资产类型"
        sortable="custom"
        prop="type"
        width="120" />
      <el-table-column
        label="主机名"
        sortable="custom"
        prop="host_name"
        width="120" />
      <el-table-column
        label="网络设备标识"
        sortable="custom"
        prop="network_device_name"
        width="140" />
      <el-table-column
        label="机房"
        sortable="custom"
        prop="idc"
        width="120" />
      <el-table-column
        label="机柜号"
        sortable="custom"
        prop="carbinet"
        width="120" />
      <el-table-column
        label="业务线"
        sortable="custom"
        prop="business"
        width="120" />
      <el-table-column
        label="资产状态"
        sortable="custom"
        prop="status"
        width="120" />
      <el-table-column label="操作">
        <template slot-scope="prop">
          <el-button size="small" type="primary">编辑</el-button>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<script>
import { asset_list } from '../../api/asset'

export default {
  name: 'AssetList',
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
        size: 5,
        order: 'asc'
      },
      sortHelp: {
        prop: '',
        order: ''
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
      asset_list(Object.assign(this.pageHelp, this.sortHelp, { serverGroup: this.serverGroupSelectModel })).then(response => {
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
