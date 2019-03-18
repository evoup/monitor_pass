<template>
  <div class="app-container">
    <el-col :span="24" class="warp-breadcrum">
      <!--搜索栏-->
      <el-col :span="24" class="toolbar">
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
      :data="list"
      stripe
      style="width: 100%">
      <el-table-column
        label="主机名"
        prop="name"
        width="180" />
      <el-table-column
        label="IP"
        prop="ip"
        width="180" />
      <el-table-column
        label="收集节点"
        prop="data_collector"
        width="180" />
      <el-table-column
        label="更新时间"
        prop="date"
        width="180" />
      <el-table-column
        label="状态"
        prop="status"
        width="180">
        <template scope="scope">
          <el-tag v-if="scope.row.status === 1" type="success">在线</el-tag>
          <el-tag v-if="scope.row.status === 2" type="danger">宕机</el-tag>
          <el-tag v-if="scope.row.status === 0" type="primary">未监控</el-tag>
        </template>
      </el-table-column>
      <el-table-column
        label="机房"
        prop="address" />
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
     具体功能查看地址：http://element.eleme.io/#/zh-CN/component/pagination
     -->
      <el-pagination :page-size="15" :total="total" background layout="total,prev,pager,next" @current-change="handleCurrentChange" />
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
        在线: 'success',
        离线: 'gray'
      }
      return statusMap[status]
    }
  },
  data() {
    return {
      filters: {
        name: '',
        type: 1
      },
      list: null,
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
      server_list(this.listQuery).then(response => {
        this.list = response.data.items
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
    // 点击分页
    handleCurrentChange(val) {
      this.pageNum = val
      this.fetchData()
    }
  }
}
</script>
