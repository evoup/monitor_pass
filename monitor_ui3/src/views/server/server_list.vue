<template>
  <div class="app-container">
    <el-row type="flex" class="row-bg">
      <el-col :span="24">
        <el-col :span="3" :offset="21">
          <div class="grid-content">
            <el-button type="primary" @click="jumpAddServer()"><i class="el-icon-plus el-icon--right" />添加服务器</el-button>
          </div>
        </el-col>
      </el-col>
    </el-row>
    <el-col :span="24" class="warp-breadcrum">
      <!--搜索栏-->
      <el-col :span="21" class="toolbar">
        <el-form :inline="true" :model="filters">
          <el-form-item>
            <template>
              <el-select v-model="filters.type" placeholder="请选择">
                <el-option v-for="item in typeData" :key="item.value" :label="item.label" :value="item.value"/>
              </el-select>
            </template>
          </el-form-item>
          <el-form-item>
            <el-input v-model="filters.name" placeholder="请输入关键字"/>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="fetchData">搜索</el-button>
          </el-form-item>
        </el-form>
      </el-col>
    </el-col>
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
        label="主机名"
        sortable="custom"
        prop="name"
        width="180" />
      <el-table-column
        label="IP"
        prop="ip"
        width="130" />
      <el-table-column
        label="收集节点"
        prop="data_collector"
        width="180" />
      <el-table-column
        label="更新时间"
        sortable="custom"
        prop="date"
        width="180" />
      <el-table-column
        label="状态"
        sortable="custom"
        prop="status"
        width="80">
        <template scope="scope">
          <el-tag v-if="scope.row.status === 1" type="success">在线</el-tag>
          <el-tag v-if="scope.row.status === 2" type="danger">宕机</el-tag>
          <el-tag v-if="scope.row.status === 0" type="primary">未监控</el-tag>
        </template>
      </el-table-column>
      <el-table-column
        label="机房"
        prop="asset.idc.name" />
      <el-table-column label="操作">
        <template scope="scope">
          <el-button size="small" type="primary" @click="lookUser(scope.$index,scope.row.u_uuid)">查看</el-button>
        </template>
      </el-table-column>
    </el-table>
    <el-col :span="24" class="toolbar block">
      <!--数据分页
     layout：分页显示的样式
     :page-size：每页显示的条数
     :total：总数
     具体功能查看地址：http://element-cn.eleme.io/#/zh-CN/component/pagination
     -->
      <!--<el-pagination :page-size="15" :total="total" background layout="total,prev,pager,next" @current-change="handleCurrentChange" />-->
      <el-pagination
        :page-sizes="[5,10,15]"
        :page-size="5"
        :total="total"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange" />
    </el-col>
  </div>
</template>

<script>
import { server_list } from '@/api/server'
import ElPager from 'element-ui/packages/pagination/src/pager'
export default {
  components: { ElPager },
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
      this.listLoading = true
      this.pageHelp.page = this.pageNum
      server_list(Object.assign(this.pageHelp, this.sortHelp)).then(response => {
        this.dataList = response.data.items
        this.pageList = response.data.page
        this.listLoading = false
        this.total = response.data.count
      })
    },
    // 下拉框初始化
    typeInfo() {
      this.typeData = [
        { value: 1, label: '全部' },
        { value: 2, label: '账户名' },
        { value: 3, label: '机构名称' },
        { value: 4, label: '手机号码' },
        { value: 5, label: '联系人' }]
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
      // console.log(column.prop + '-' + column.order)
      this.sortHelp.order = column.order
      this.sortHelp.prop = column.prop
      this.fetchData()
    },
    // 跳转到服务器添加页面
    jumpAddServer() {
      this.$router.push({ path: '/add_server' })
    }
  }
}
</script>
