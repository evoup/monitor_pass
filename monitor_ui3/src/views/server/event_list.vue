<template>
  <div class="app-container">
    <el-form ref="form">
      <el-form-item label="筛选">
        <el-row>
          <el-col :span="18">
            <el-select
              v-model="serverGroupSelectModel"
              placeholder="请选择服务器组"
              @change="fetchData"
            >
              <el-option
                v-for="item in serverGroups"
                :key="item.id"
                :label="item.name"
                :aria-selected="true"
                :value="item.id"
              />
            </el-select>
            <el-select
              v-model="serverSelectModel"
              placeholder="请选择服务器"
            >
              <el-option
                v-for="item in servers"
                :key="item.id"
                :label="item.name"
                :aria-selected="true"
                :value="item.id"
              />
            </el-select>
          </el-col>
        </el-row>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { server_group_list, server_list } from '../../api/server'

export default {
  name: 'EventList',
  data() {
    return {
      serverGroupSelectModel: 0,
      serverSelectModel: null,
      serverGroups: [],
      servers: [],
      listLoading: true,
      total: 0,
      pageNum: 1,
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
        page: 1,
        size: 7,
        order: 'asc'
      },
      sortHelp: {
        prop: '',
        order: ''
      }
    }
  },
  created() {
    this.fetchData()
    this.fetchServerGroupListData()
  },
  methods: {
    fetchData() {
      this.listLoading = true
      this.pageHelp.page = this.pageNum
      server_list(Object.assign(this.pageHelp, this.sortHelp, { serverGroup: this.serverGroupSelectModel })).then(response => {
        this.dataList = response.data.items
        this.pageList = response.data.page
        this.listLoading = false
        this.total = response.data.count
      })
    },
    fetchServerGroupListData() {
      server_group_list().then(response => {
        this.serverGroups = response.data.items
        this.serverGroups.push({ id: 0, name: '所有', desc: null, alarm_type: null })
      })
    }
  }
}
</script>

<style scoped>

</style>
