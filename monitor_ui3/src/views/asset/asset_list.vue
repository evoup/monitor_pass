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
        prop="asset_type"
        width="120" />
      <el-table-column
        label="名称"
        sortable="custom"
        prop="name"
        width="120" />
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
export default {
  name: 'AssetList'
}
</script>

<style scoped>

</style>
