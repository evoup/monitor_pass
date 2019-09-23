<template>
  <div class="app-container">
    <el-form ref="form">
      <el-form-item>
        <el-row>
          <el-col :span="24" align="right">
            <div class="grid-content">
              <el-button type="primary" @click="jumpAddOperation"><i class="el-icon-plus el-icon--right"/>添加操作
              </el-button>
            </div>
          </el-col>
        </el-row>
      </el-form-item>
    </el-form>
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
        label="名称"
        sortable="custom"
        prop="name"
        min-width="15%"/>
      <el-table-column
        label="条件"
        sortable="custom"
        prop="condition"
        min-width="15%"/>
      <el-table-column
        label="操作项"
        sortable="custom"
        prop="operation_items"
        min-width="50%">
        <template slot-scope="prop">
          <ul>
            <li v-for="obj in prop.row.operation_items" :key="obj.id">{{ obj.name }}
              (<el-link size="small" type="primary">编辑</el-link>
              <el-link size="small" type="primary">删除</el-link>)
            </li>
          </ul>
        </template>
      </el-table-column>
      <el-table-column
        label="状态"
        sortable="custom"
        prop="status"
        min-width="10%">
        <template slot-scope="prop">
          <el-tag v-if="prop.row.status === 0" type="info">禁用</el-tag>
          <el-tag v-if="prop.row.status === 1" type="success">启用</el-tag>
        </template>
      </el-table-column>
      <el-table-column>
        <template slot-scope="prop" min-width="10%">
          <el-button size="small" type="primary">添加操作项</el-button>
          <el-button size="small" type="primary">编辑操作</el-button>
          <el-button size="small" type="danger" @click="deleteIdc(prop.row.id, prop.$index)">删除操作</el-button>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<script>
import { operation_list } from '../../api/operation'

export default {
  name: 'OperationList',
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
      operation_list(Object.assign(this.pageHelp, this.sortHelp)).then(response => {
        this.dataList = response.data.items
        this.pageList = response.data.page
        this.listLoading = false
        this.total = response.data.count
      })
    },
    jumpAddOperation() {
      this.$router.push({ path: '/add_operation' })
    }
  }
}
</script>

<style scoped>

</style>
