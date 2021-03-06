<template>
  <div class="app-container">
    <el-row type="flex" class="row-bg">
      <el-col :span="24">
        <el-col :span="3" :offset="21">
          <div class="grid-content">
            <el-button
              type="primary"
              @click="jumpAddTemplate()"
            ><i class="el-icon-plus el-icon--right" />添加模板
            </el-button>
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
      @sort-change="sortChange"
    >
      <el-table-column
        :index="indexMethod"
        prop="id"
        label="序号"
        type="index"
        width="80"
        align="center"
      />
      <el-table-column
        label="模板名"
        sortable="custom"
        prop="name"
        width="220"
      />
      <el-table-column label="监控项数" prop="items" width="130">
        <template slot-scope="prop">
          <div align="center">
            <el-link type="primary" @click="jumpItemList(prop.row.id)">{{
              prop.row.items
            }}</el-link>
          </div>
        </template>
      </el-table-column>
      <el-table-column label="触发器数" prop="triggers" width="130">
        <template slot-scope="prop">
          <div align="center">
            <el-link type="primary" @click="jumpTriggerList(prop.row.id)">{{
              prop.row.triggers
            }}</el-link>
          </div>
        </template>
      </el-table-column>
      <el-table-column label="图表数" prop="diagram" width="130">
        <template slot-scope="prop">
          <div align="center">
            <el-link type="primary" @click="jumpDiagramList(prop.row.id)">{{
              prop.row.diagrams
            }}</el-link>
          </div>
        </template>
      </el-table-column>
      <el-table-column>
        <template slot-scope="prop">
          <el-button
            size="small"
            type="primary"
            @click="jumpChangeTemplate(prop.row.id)"
          >编辑</el-button
          >
          <el-button
            size="small"
            type="danger"
            @click="deleteTemplate(prop.row.id, prop.$index)"
          >删除</el-button
          >
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
      <el-pagination
        :page-sizes="[5, 10, 15]"
        :page-size="5"
        :total="total"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    </el-col>
  </div>
</template>

<!--suppress JSUnusedGlobalSymbols -->
<script>
import { template_list, delete_template } from '../../api/template'
import ElPager from 'element-ui/packages/pagination/src/pager'

export default {
  components: { ElPager },
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
      this.listLoading = true
      this.pageHelp.page = this.pageNum
      template_list(Object.assign(this.pageHelp, this.sortHelp)).then(
        response => {
          this.dataList = response.data.items
          this.pageList = response.data.page
          this.listLoading = false
          this.total = response.data.count
        }
      )
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
    // 跳转到模板添加页面
    jumpAddTemplate() {
      this.$router.push({ path: '/add_template' })
    },
    // 跳转到模板修改页面
    jumpChangeTemplate(id) {
      this.$router.push({
        path: '/change_template',
        query: { id: id }
      })
    },
    jumpItemList(id) {
      this.$router.push({
        path: '/item_list',
        query: { template_id: id }
      })
    },
    jumpTriggerList(id) {
      this.$router.push({
        path: '/trigger_list',
        query: { template_id: id }
      })
    },
    jumpDiagramList(id) {
      this.$router.push({
        path: '/diagram_list',
        query: { template_id: id }
      })
    },
    // 删除当前行
    deleteRow(index, rows) {
      rows.splice(index, 1)
    },
    // 删除模板
    deleteTemplate(id, rowIdx) {
      delete_template({ id: id }).then(response => {
        this.deleteRow(rowIdx, this.dataList)
      })
    }
  }
}
</script>
