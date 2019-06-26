<template>
  <div class="app-container">
    <el-tabs type="border-card">
      <!-- tab-pane1 -->
      <el-tab-pane label="基本信息">
        <el-form ref="form" :model="form" label-width="120px">
          <el-form-item label="服务器组名：">
            <el-col :span="8">
              <el-input v-model="form.name" placeholder="请输入服务器组名" />
            </el-col>
          </el-form-item>
          <el-form-item label="备注：">
            <el-col :span="8">
              <el-input
                v-model="form.desc"
                class="note"
                placeholder="请输入备注"
                type="textarea"
              />
            </el-col>
          </el-form-item>
          <el-form-item label="接收告警类型：">
            <el-col :span="8">
              <el-select v-model="optionValue" placeholder="请选择">
                <el-option
                  v-for="(item, index) in options"
                  :key="index"
                  :label="item.label"
                  :value="item.value"
                />
              </el-select>
            </el-col>
          </el-form-item>
          <el-form-item>
            <el-button
              type="primary"
              @click="
                addServerGroup(
                  form.name,
                  form.desc,
                  optionValue,
                  memberUserGroupIds,
                  templateSelectModel
                )
              "
            >创建</el-button
            >
            <el-button @click="jumpServerGroupList">取消</el-button>
          </el-form-item>
        </el-form>
      </el-tab-pane>
      <!-- tab-pane2 -->
      <el-tab-pane label="成员用户组">
        <el-table
          :v-loading="listLoading"
          :data="dataList"
          stripe
          border
          tooltip-effect="dark"
          style="width: 100%"
        >
          <el-table-column
            prop="id"
            label="序号"
            type="index"
            width="80"
            align="center"
          />
          <el-table-column
            label="用户组名"
            sortable="custom"
            prop="name"
            width="120"
          />
          <el-table-column label="操作">
            <template slot-scope="prop">
              <el-switch
                v-model="prop.row.belong_group"
                active-value="1"
                inactive-value="0"
                @change="change_member($event, prop.row)"
              />
            </template>
          </el-table-column>
        </el-table>
      </el-tab-pane>
      <!-- tab-pane3 -->
      <el-tab-pane label="模板">
        <el-select
          v-model="templateSelectModel"
          multiple
          placeholder="请选择模板（可选）"
          style="width: 80%"
        >
          <el-option
            v-for="item in templateData"
            :key="item.id"
            :label="item.name"
            :aria-selected="true"
            :value="item.id"
          />
        </el-select>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<!--suppress JSUnusedGlobalSymbols -->
<script>
import { user_group_list } from '../../api/user'
import { template_list } from '../../api/template'
import { add_server_group } from '../../api/server'

export default {
  data() {
    return {
      options: [
        {
          value: '1',
          label: '所有告警'
        },
        {
          value: '2',
          label: '严重告警'
        },
        {
          value: '3',
          label: '普通告警'
        },
        {
          value: '4',
          label: '不接收'
        }
      ],
      optionValue: '1',
      form: {
        name: '',
        desc: '',
        templates: []
      },
      dataList: [], // 列表数据
      listLoading: true,
      templateSelectModel: [],
      templateData: [],
      // 加入改组的用户组id
      memberUserGroupIds: new Set([])
    }
  },
  mounted() {
    this.fetchData()
  },
  methods: {
    // 获取所有模板列表
    fetchTemplateListData() {
      template_list().then(response => {
        this.templateData = response.data.items
      })
    },
    fetchData() {
      this.listLoading = true
      user_group_list().then(response => {
        this.dataList = response.data.items
        this.listLoading = false
        this.fetchTemplateListData()
      })
    },
    change_member(a, b) {
      if (a === '1') {
        const member = b.id
        this.memberUserGroupIds.add(member)
      }
    },
    addServerGroup(a, b, c, d, e) {
      // set 转换为[]
      d = Array.from(d)
      add_server_group(a, b, c, d, e)
    },
    jumpServerGroupList() {
      this.$router.push({ path: '/server_group_list' })
    }
  }
}
</script>

<style>
.note textarea {
  height: 100px !important;
}
</style>
