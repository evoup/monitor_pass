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
        label="机房"
        sortable="custom"
        prop="name"
        width="180" />
      <el-table-column
        label="楼层"
        sortable="custom"
        prop="floor"
        width="330" />
      <el-table-column>
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
import { idc_list, delete_idc } from '../../api/idc'
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
      idc_list(Object.assign(this.pageHelp, this.sortHelp)).then(response => {
        this.dataList = response.data.items
        this.pageList = response.data.page
        this.listLoading = false
        this.total = response.data.count
      })
    },
    // 跳转到机房添加页面
    jumpAddIdc() {
      this.$router.push({ path: '/add_idc' })
    },
    // 删除当前行
    deleteRow(index, rows) {
      rows.splice(index, 1)
    },
    // 删除机房
    deleteIdc(id, rowIdx) {
      console.log(id)
      delete_idc({ id: id }).then(response => {
        this.deleteRow(rowIdx, this.dataList)
      })
    }
  }
}
</script>

<style scoped>

</style>
