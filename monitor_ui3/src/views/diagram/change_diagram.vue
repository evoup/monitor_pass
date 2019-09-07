<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="名称">
        <el-col :span="8">
          <el-input v-model="form.name" placeholder="请输入图表名称"/>
        </el-col>
      </el-form-item>
      <el-form-item label="别名">
        <el-col :span="8">
          <el-input v-model="form.alias" placeholder="请输入别名(可选)"/>
        </el-col>
      </el-form-item>
      <el-form-item label="宽度">
        <el-col :span="3">
          <el-input v-model="form.width" placeholder="请输入宽度"/>
        </el-col>
      </el-form-item>
      <el-form-item label="高度">
        <el-col :span="3">
          <el-input v-model="form.height" placeholder="请输入高度"/>
        </el-col>
      </el-form-item>
      <el-form-item label="监控项">
        <el-table
          :v-loading="listLoading"
          :data="dataList"
          stripe
          border
          tooltip-effect="dark"
          style="width: 80%">
          <el-table-column prop="id" label="序号" type="index" width="80" align="center"/>
          <el-table-column
            label="名称"
            sortable="custom"
            prop="item"
            min-width="40%"/>
          <el-table-column
            label="取值方式"
            sortable="custom"
            prop="function"
            min-width="30%">
            <el-select v-model="functionSelectModel" placeholder="请选择">
              <el-option
                v-for="item in functionOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value"/>
            </el-select>
          </el-table-column>
          <el-table-column label="操作" min-width="20%">
            <template slot-scope="prop">
              <el-button size="small" type="primary" @click="jumpChangeUser(prop.row.id)">编辑</el-button>
              <el-button size="small" type="danger" @click="deleteUser(prop.row.id, prop.$index)">删除</el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-form-item>
      <el-form-item>
        <el-link :underline="false" type="primary">添加监控项到图表</el-link>
      </el-form-item>
      <el-form-item>
        <el-button
          type="primary"
          @click="x"
        >更新
        </el-button
        >
        <el-button @click="jumpDiagramList">取消</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { diagram_info, diagram_list } from '../../api/diagram'

export default {
  name: 'ChangeDiagram',
  data() {
    return {
      form: {
        name: '',
        alias: '',
        width: 900,
        height: 200
      },
      functionOptions: [[{
        value: 'avg',
        label: 'avg'
      }]],
      functionSelectModel: 'avg',
      listLoading: true,
      dataList: []
    }
  },
  created() {
    this.getData()
  },
  methods: {
    getData() {
      diagram_list({ id: 1 }).then(
        response => {
          this.form.name = response.data.items[0].name
          this.form.width = response.data.items[0].width
          this.form.height = response.data.items[0].height
        })
      diagram_info({ id: 1 }).then(response => {
        this.dataList = response.data.items
        this.pageList = response.data.page
        this.listLoading = false
        this.total = response.data.count
      })
    },
    jumpDiagramList() {
      this.$router.push({ path: '/diagram_list' })
    }
  }
}
</script>

<style scoped>
  .app-container /deep/ .el-form-item {
    margin-bottom: 8px;
  }
</style>
