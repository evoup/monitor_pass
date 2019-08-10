<template>
  <div class="app-container">
    <el-col :span="24">
      <el-table
        :v-loading="listLoading"
        :data="dataList"
        stripe
        border
        tooltip-effect="dark"
        style="width: 100%;margin-top:10px"
      >
        <el-table-column prop="id" label="序号" type="index" width="80" align="center"/>
        <el-table-column
          label="资产类型"
          sortable="custom"
          prop="device_type_id"
          width="110">
          <template slot-scope="prop">
            <el-tag v-if="prop.row.device_type_id === 1">服务器</el-tag>
            <el-tag v-if="prop.row.device_type_id === 2">交换机</el-tag>
            <el-tag v-if="prop.row.device_type_id === 3">防火墙</el-tag>
            <el-tag v-if="prop.row.device_type_id > 3">--</el-tag>
          </template>
        </el-table-column>
        <el-table-column
          label="主机名"
          sortable="custom"
          prop="host_name"
          width="120"/>
        <el-table-column
          label="网络设备标识"
          sortable="custom"
          prop="network_device_name"
          width="140"/>
        <el-table-column
          label="机房"
          sortable="custom"
          prop="idc.name"
          width="120"/>
        <el-table-column
          label="机柜号"
          sortable="custom"
          prop="carbinet"
          width="100"/>
        <el-table-column
          label="业务线"
          sortable="custom"
          prop="business"
          width="120"/>
        <el-table-column
          label="资产状态"
          sortable="custom"
          prop="device_status_id"
          width="120">
          <template slot-scope="prop">
            <el-tag v-if="prop.row.device_status_id === 1" type="success">上架</el-tag>
            <el-tag v-if="prop.row.device_status_id === 2" type="danger">在线</el-tag>
            <el-tag v-if="prop.row.device_status_id === 3" type="primary">离线</el-tag>
            <el-tag v-if="prop.row.device_status_id === 4" type="primary">下架</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作">
          <template slot-scope="prop">
            <el-button size="small" type="primary">查看详情</el-button>
            <el-button size="small" type="primary">编辑</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-col>
    <el-col :span="24" class="toolbar block">
      <!--数据分页
   layout：分页显示的样式
   :page-size：每页显示的条数
   :total：总数
   具体功能查看地址：http://element-cn.eleme.io/#/zh-CN/component/pagination
   -->
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
        size: 7,
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
