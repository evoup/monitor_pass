<template>
  <div class="app-container">
    <el-tabs type="border-card">
      <!-- 面板1 -->
      <el-tab-pane label="基本信息">
        <el-form ref="form" :model="form" label-width="120px">
          <el-form-item label="组名：">
            <el-col :span="8">
              <el-input v-model="form.name" placeholder="请输入组名"/>
            </el-col>
          </el-form-item>
          <el-form-item label="备注：">
            <el-col :span="8">
              <el-input v-model="form.desc" class="note" placeholder="请输入备注" type="textarea" />
            </el-col>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="addUserGroup(form.name, form.desc, transferModel, memberUserProfileIds)">创建</el-button>
            <el-button @click="jumpUserGroupList">取消</el-button>
          </el-form-item>
        </el-form>
      </el-tab-pane>
      <!-- 面板2 -->
      <el-tab-pane label="权限">
        <template>
          <template>
            <el-transfer v-model="transferModel" :data="transferData" :titles="['未指派权限', '已指派权限']" :button-texts="['到左边', '到右边']" />
          </template>
        </template>
      </el-tab-pane>
      <!-- 面板3 -->
      <el-tab-pane label="成员">
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
            label="用户名"
            sortable="custom"
            prop="first_name"
            width="120" />
          <el-table-column
            label="描述"
            prop="profile.desc"
            width="380" />
          <el-table-column label="是否用户组成员">
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
    </el-tabs>
  </div>
</template>

<!--suppress JSUnusedGlobalSymbols -->
<script>
import { add_user_group, user_list, user_perm_list } from '../../api/user'
export default {
  data() {
    return {
      options: [{
        value: '1',
        label: '所有告警'
      }, {
        value: '2',
        label: '严重告警'
      }, {
        value: '3',
        label: '普通告警'
      }, {
        value: '4',
        label: '不接收'
      }],
      optionValue: '1',
      form: {
        name: '',
        desc: '',
        priv: '',
        members: []
      },
      dataList: [], // 列表数据
      // 列表前端分页
      pageList: {
        totalCount: '',
        pageSize: '',
        totalPage: '',
        currPage: ''
      },
      // 列表分页辅助类(传参)
      pageHelp: {
        page: 1, // 和后端参数一样
        size: 5, // 后端参数为size
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
      // -------用户权限---------
      // v-model传递的是django权限的codename字符串数值，e.g. add_group
      transferModel: [],
      // 接收后端权限数据用
      transferData: [],
      // 加入改组的用户id
      memberUserProfileIds: new Set([])
    }
  },
  mounted() {
    this.fetchUserListData()
    this.fetchUserPermListData()
  },
  methods: {
    fetchUserListData() {
      this.pageHelp.page = this.pageNum
      user_list(Object.assign(this.pageHelp, this.sortHelp)).then(response => {
        this.dataList = response.data.items
        this.pageList = response.data.page
        this.listLoading = false
        this.total = response.data.count
      })
    },
    indexMethod(index) {
      return (this.pageList.currPage - 1) * this.pageList.pageSize + index + 1
    },
    handleSizeChange(val) {
      this.pageList.pageSize = val
      this.pageHelp.size = this.pageList.pageSize
      this.pageHelp.page = this.pageList.currPage
      this.fetchUserListData()
    },
    // 点击分页sort-change
    handleCurrentChange(val) {
      this.pageNum = val
      this.fetchUserListData()
    },
    sortChange(column, prop, order) {
      this.sortHelp.order = column.order
      this.sortHelp.prop = column.prop
      this.fetchData()
    },
    //
    generateTransferData(items) {
      const data = []
      for (let i = 0; i < (items).length; i++) {
        data.push({
          key: items[i].codename,
          label: items[i].name
        })
      }
      this.transferData = data
    },
    // -----------------获取用户权限列表 Start--------------------------------
    fetchUserPermListData() {
      user_perm_list().then(response => {
        this.generateTransferData(response.data.items)
      })
    },
    // -----------------获取用户权限列表 End----------------------------------

    // -----------------点击el-switch时传递选中的成员用户 Start-----------------
    change_member(a, b) {
      if (a === '1') {
        const member = b.profile.id
        this.memberUserProfileIds.add(member)
      }
    },
    // -----------------点击el-switch时传递选中的成员用户 End-------------------
    addUserGroup(a, b, c, d) {
      // set 转换为[]
      d = Array.from(d)
      add_user_group(a, b, c, d)
    },
    // 跳转到用户组列表页面
    jumpUserGroupList() {
      this.$router.push({ path: '/user_group_list' })
    }
  }
}
</script>

<style scoped>
  .note textarea {
    height: 100px !important;
  }
  .app-container /deep/ .el-transfer-panel{
    border: 1px solid #ebeef5;

    border-radius: 4px;

    overflow: hidden;

    background: #fff;

    display: inline-block;

    vertical-align: middle;

    width: 250px;

    -webkit-box-sizing: border-box;

    box-sizing: border-box;

    position: relative;

  }
</style>
