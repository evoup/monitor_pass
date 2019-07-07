<template>
  <div class="app-container">
    <el-row type="flex" class="row-bg">
      <el-col :span="24">
        <el-col :span="3" :offset="21">
          <div class="grid-content">
            <el-button type="primary" @click="jumpAddItem()"><i class="el-icon-plus el-icon--right"/>添加触发器
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
      style="width: 100%"
      @sort-change="sortChange">
      <el-table-column :index="indexMethod" prop="id" label="序号" type="index" width="80" align="center"/>
      <el-table-column
        label="触发器名称"
        sortable="custom"
        prop="name"
        width="220"/>
      <el-table-column
        label="表达式"
        prop="triggers"
        width="100">
        <template slot-scope="prop">
          <div align="center">
            <el-link type="primary" @click="jumpChangeItem(prop.row.id)">{{ prop.row.triggers }}</el-link>
          </div>
        </template>
      </el-table-column>
      <el-table-column
        label="告警等级"
        prop="key"
        width="300"/>
      <el-table-column
        label="状态"
        prop="status"
        width="100">
        <template slot-scope="prop">
          <el-switch
            v-model="prop.row.status"
            active-value="1"
            inactive-value="0"
            @change="changeItemStatus(prop.row.id, $event)"
          />
        </template>
      </el-table-column>
      <el-table-column label="操作">
        <template slot-scope="prop">
          <el-button size="small" type="primary" @click="jumpChangeItem(prop.row.id)">编辑</el-button>
          <el-button size="small" type="danger">删除</el-button>
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
        :page-sizes="[5,10,15]"
        :page-size="5"
        :total="total"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"/>
    </el-col>
  </div>
</template>

<script>
export default {
  name: 'TriggerList'
}
</script>

<style scoped>

</style>
