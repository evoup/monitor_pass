<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="名称">
        <el-col :span="8">
          <el-input v-model="form.name" placeholder="请输入图表名称"/>
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
          :data="totalSelectedItemsList"
          stripe
          border
          tooltip-effect="dark"
          style="width: 80%">
          <el-table-column prop="id" label="序号" type="index" width="80" align="center"/>
          <el-table-column
            label="名称"
            sortable="custom"
            prop="name"
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
              <el-button size="small" type="primary">编辑</el-button>
              <el-button size="small" type="danger">删除</el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-form-item>
      <el-form-item>
        <el-link :underline="false" type="primary" @click="addItemToDiagram">添加监控项到图表</el-link>
      </el-form-item>
      <el-form-item>
        <el-button
          type="primary"
        >更新
        </el-button
        >
        <el-button @click="jumpDiagramList">取消</el-button>
      </el-form-item>
    </el-form>

    <el-dialog :visible.sync="dialogFormVisible" title="添加监控项到图表">
      <el-select
        v-model="templateSelectModel"
        placeholder="请选择模板"
        style="width: 80%"
        @change="fetchTemplateListData"
      >
        <el-option
          v-for="item in templateData"
          :key="item.id"
          :label="item.name"
          :aria-selected="true"
          :value="item.id"
        />
      </el-select>
      <el-select
        v-model="itemSelectModel"
        placeholder="请选择监控项"
        style="width: 80%"
      >
        <el-option
          v-for="item in itemData"
          :key="item.id"
          :label="item.name"
          :aria-selected="true"
          :value="item.id"
        />
      </el-select>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogFormVisible = false">取 消</el-button>
        <el-button
          type="primary"
          @click="addItemToList"
        >确 定
        </el-button
        >
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { template_list } from '../../api/template'
import { item_list } from '../../api/item'

export default {
  name: 'AddDiagram',
  data() {
    return {
      form: {
        name: '',
        width: '100%',
        height: '200'
      },
      functionOptions: [[{
        value: 'avg',
        label: 'avg'
      }]],
      functionSelectModel: 'avg',
      listLoading: true,
      dialogFormVisible: false,
      templateSelectModel: [],
      templateData: [],
      itemSelectModel: [],
      itemData: [],
      // 展示的监控项列表
      totalSelectedItemsList: [],
      // 展示的监控项id
      totalSelectedItemsIds: []
    }
  },
  created() {
    this.getData()
  },
  methods: {
    getData() {
      this.fetchTemplateListData()
    },
    addItemToDiagram() {
      this.dialogFormVisible = true
    },
    // 获取所有模板列表
    fetchTemplateListData() {
      this.itemData = null
      template_list({ page: 1, size: 99999, order: 'asc' }).then(response => {
        this.templateData = response.data.items
        this.fetchItemData()
      })
    },
    fetchItemData() {
      let cond = null
      if (this.templateSelectModel != null && this.templateSelectModel !== '') {
        cond = { template_id: this.templateSelectModel }
      }
      item_list(Object.assign({ size: 99999 }, cond)).then(
        response => {
          this.itemData = response.data.items
        }
      )
    },
    addItemToList() {
      console.log('itemSelectModel' + this.itemSelectModel)
      this.dialogFormVisible = false
      if (!this.totalSelectedItemsIds.includes(this.itemSelectModel)) {
        this.totalSelectedItemsIds.push(this.itemSelectModel)
      }
      item_list(Object.assign({ size: 99999 }, { ids: this.totalSelectedItemsIds.join() })).then(
        response => {
          this.totalSelectedItemsList = []
          for (var i in response.data.items) {
            console.log('utem:' + response.data.items[i])
            this.totalSelectedItemsList.push(response.data.items[i])
          }
        }
      )
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
