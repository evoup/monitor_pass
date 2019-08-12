<template>
  <div class="app-container">
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
        label="内容"
        sortable="custom"
        prop="content"
        width="180" />
      <el-table-column
        label="创建"
        sortable="custom"
        prop="create_id"
        width="330" />
      <el-table-column
        label="创建日期"
        sortable="custom"
        prop="create_date"
        width="330" />
      <el-table-column label="操作">
        <template slot-scope="prop">
          <el-button size="small" type="primary">编辑</el-button>
          <el-button size="small" type="danger" @click="deleteIdc(prop.row.id, prop.$index)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<script>
import { asset_record_list } from '../../api/asset'

export default {
  name: 'AssetRecordList',
  methods: {
    created() {
      this.fetchData()
    },
    fetchData() {
      this.listLoading = true
      this.pageHelp.page = this.pageNum
      asset_record_list(Object.assign(this.pageHelp, this.sortHelp)).then(response => {
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
