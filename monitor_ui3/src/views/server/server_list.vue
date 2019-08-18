<template>
  <div class="app-container">
    <el-form ref="form">
      <el-form-item label="服务器组">
        <el-row>
          <el-col :span="18">
            <el-select
              v-model="serverGroupSelectModel"
              placeholder="请选择服务器组"
              @change="fetchData"
            >
              <el-option
                v-for="item in serverGroups"
                :key="item.id"
                :label="item.name"
                :aria-selected="true"
                :value="item.id"
              />
            </el-select>
          </el-col>
          <el-col :span="4" align="right">
            <div class="grid-content">
              <el-button type="primary" @click="jumpAddServer()"><i class="el-icon-plus el-icon--right" />添加服务器</el-button>
            </div>
          </el-col>
        </el-row>
      </el-form-item>
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
            <template slot-scope="prop">
              <el-tag v-if="prop.row.status === 0" type="success">在线</el-tag>
              <el-tag v-if="prop.row.status === 1" type="danger">宕机</el-tag>
              <el-tag v-if="prop.row.status === 2" type="primary">未监控</el-tag>
            </template>
          </el-table-column>
          <el-table-column
            label="机房"
            prop="asset.idc.name" />
          <el-table-column label="操作">
            <template slot-scope="prop">
              <el-button size="small" type="primary" @click="jumpServerDetail(prop.row.id)">查看</el-button>
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
        <!--<el-pagination :page-size="15" :total="total" background layout="total,prev,pager,next" @current-change="handleCurrentChange" />-->
        <el-pagination
          :page-sizes="[7,10,15]"
          :page-size="7"
          :total="total"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange" />
      </el-col>
  </el-form></div>
</template>

<!--suppress JSUnusedGlobalSymbols -->
<script>
import { server_group_list, server_list } from '../../api/server'
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
    this.fetchServerGroupListData()
  },
  methods: {
    fetchData() {
      this.listLoading = true
      this.pageHelp.page = this.pageNum
      server_list(Object.assign(this.pageHelp, this.sortHelp, { serverGroup: this.serverGroupSelectModel })).then(response => {
        this.dataList = response.data.items
        this.pageList = response.data.page
        this.listLoading = false
        this.total = response.data.count
      })
    },
    fetchServerGroupListData() {
      server_group_list().then(response => {
        this.serverGroups = response.data.items
        this.serverGroups.push({ id: 0, name: '所有', desc: null, alarm_type: null })
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
    // 跳转到服务器添加页面
    jumpAddServer() {
      this.$router.push({ path: '/add_server' })
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
