<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="名称">
        <el-col :span="8">
          <el-input v-model="form.name" placeholder="请输入图表名称"/>
        </el-col>
      </el-form-item>
      <el-form-item label="宽度">
        <el-col :span="8">
          <el-input v-model="form.width" placeholder="请输入宽度"/>
        </el-col>
      </el-form-item>
      <el-form-item label="高度">
        <el-col :span="8">
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
          style="width: 100%"
          @sort-change="sortChange">
          <el-table-column :index="indexMethod" prop="id" label="序号" type="index" width="80" align="center" />
          <el-table-column
            label="名称"
            sortable="custom"
            prop="name"
            min-width="40%" />
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
        <el-button
          type="primary"
          @click="x"
        >更新
        </el-button
        >
        <el-button @click="jumpServerList">取消</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
export default {
  name: 'ChangeDiagram',
  data() {
    return {
      form: {
        name: '',
        width: null,
        height: null
      },
      functionOptions: [{
        value: '选项1',
        label: '黄金糕'
      }],
      functionSelectModel: null
    }
  },
  methods: {
    x(){}
  }
}
</script>

<style scoped>

</style>
