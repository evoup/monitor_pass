<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="触发器名称">
        <el-col :span="8">
          <el-input v-model="form.name" placeholder="请输入触发器名称"/>
        </el-col>
      </el-form-item>
      <el-form-item label="表达式">
        <el-col :span="24">
          <el-row :gutter="20">
            <el-col :span="10">
              <el-input
                v-model="form.expression"
                placeholder="请输入表达式"
                type="textarea"
              />
            </el-col>
            <el-col :span="10">
              <el-button
                v-if="form.expression.length<=0"
                type="primary"
                @click="addCondition(logicType.SINGLE)"
              >添加条件
              </el-button
              >
            </el-col>
            <el-col :span="10">
              <el-button
                v-if="form.expression.length>0"
                type="primary"
                @click="addCondition(logicType.AND)"
              >添加AND条件
              </el-button
              >
              <el-button
                v-if="form.expression.length>0"
                type="primary"
                @click="addCondition(logicType.OR)"
              >添加OR条件
              </el-button
              >
            </el-col>
          </el-row>
        </el-col>
      </el-form-item>
      <el-form-item label="描述">
        <el-col :span="24">
          <el-row :gutter="20">
            <el-col :span="10">
              <el-input
                v-model="form.desc"
                placeholder="请输入描述"
                type="textarea"
              />
            </el-col>
          </el-row>
        </el-col>
      </el-form-item>
      <el-form-item label="严重等级">
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
      <el-form-item label="只告警一次">
        <el-col :span="24">
          <el-row :gutter="20">
            <el-col :span="10">
              <el-switch
                v-model="switchValue"
                active-value="1"
                inactive-value="0"
              />
            </el-col>
          </el-row>
        </el-col>
      </el-form-item>
      <el-form-item label="启用">
        <el-col :span="24">
          <el-row :gutter="20">
            <el-col :span="10">
              <el-switch
                v-model="switchStatus"
                active-value="1"
                inactive-value="0"
              />
            </el-col>
          </el-row>
        </el-col>
      </el-form-item>
      <el-form-item>
        <el-button
          type="primary"
          @click="
            addTrigger(form.name, form.expression, form.desc, switchStatus, form.level)
          "
        >创建
        </el-button
        >
        <el-button @click="jumpTriggerList">取消</el-button>
      </el-form-item>
      <!--浮层对话框-->
      <el-dialog :visible.sync="dialogFormVisible" title="添加触发器表达式条件">
        <el-form :model="form">
          <el-form-item
            :label-width="formLabelWidth"
            label="选择模板或服务器组"
          >
            <el-radio-group v-model="radio" @change="selectType">
              <el-radio-button label="template">模板</el-radio-button>
              <el-radio-button label="server_group">服务器组</el-radio-button>
            </el-radio-group>
          </el-form-item>
          <el-form-item
            :label-width="formLabelWidth"
            :label="selectTemplateOrServerGroup"
          >
            <el-select
              v-model="templateOrServerGroupSelectModel"
              placeholder=""
              style="width: 80%"
              @change="fetchMonitorItemListData"
            >
              <el-option
                v-for="item in templateOrServerGroupData"
                :key="item.id"
                :label="item.name"
                :aria-selected="true"
                :value="item.id"
              />
            </el-select>
          </el-form-item>
          <el-form-item
            :label-width="formLabelWidth"
            label="请选择监控项"
          >
            <!--坑点：要么不设置value-key，设置了就要设置为value，原因不明-->
            <el-select v-model="ItemSelectModel" value-key="value" placeholder="请选择监控项" style="width: 80%">
              <el-option v-for="item in monitorItemListData" :key="item.value" :label="item.name" :value="item">
                <span style="float: left">{{ item.name }}</span>
                <span style="float: right; color: #8492a6; font-size: 13px;padding-left: 20px">键：{{ item.key }}</span>
              </el-option>
            </el-select>
          </el-form-item>
        </el-form>
        <el-form-item
          :label-width="formLabelWidth"
          label="请选择函数"
        >
          <el-select
            v-model="functionSelectModel"
            style="width: 80%"
            placeholder="请选择函数"
            @change="changeData(functionSelectModel.component)"
          >
            <el-option
              v-for="item in functionOptions"
              :key="item.value"
              :label="item.label"
              :value="item"/>
          </el-select>
        </el-form-item>
        <!--参数子组件-->
        <component ref="paramFormComp" :is="componentFile" />
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">取 消</el-button>
          <el-button
            type="primary"
            @click="() => {addExpressionCondition(ItemSelectModel.key, specificLogicType)}"
          >确 定
          </el-button
          >
        </div>
      </el-dialog>
    </el-form>
  </div>
</template>

<script>
import { template_list } from '../../api/template'
import { server_group_list } from '../../api/server'
import { item_list } from '../../api/item'
// 必须预先加载，不然会闪烁
import './components/last_gt'
import './components/last_lt'
import './components/last_eq'
import './components/last_neq'
import './components/avg_gt'
import './components/avg_lt'
import './components/avg_eq'
import './components/avg_neq'
import './components/diff_eq'
import { add_trigger } from '../../api/trigger'

export default {
  name: 'CreateTrigger',
  props: {
    componentName: {
      type: String,
      default: 'last_eq',
      useDefaultForNull: true
    }
  },
  data() {
    return {
      logicType: {
        SINGLE: 'single',
        AND: 'and',
        OR: 'or'
      },
      form: {
        name: '',
        expression: '',
        desc: '',
        level: 0
      },
      options: [
        {
          value: '1',
          label: '信息'
        },
        {
          value: '2',
          label: '警告'
        },
        {
          value: '3',
          label: '严重警告'
        },
        {
          value: '4',
          label: '灾难警告'
        }
      ],
      functionOptions: [{
        value: 'last[=]',
        label: '最末(最近) T值是N',
        component: 'last_eq'
      }, {
        value: 'last[>]',
        label: '最末(最近) T值大于N',
        component: 'last_gt'
      }, {
        value: 'last[<]',
        label: '最末(最近) T值小于N',
        component: 'last_lt'
      }, {
        value: 'last[#]',
        label: '最末(最近) T值不是N',
        component: 'last_neq'
      }, {
        value: 'avg[=]',
        label: '期间T的平均值是N',
        component: 'avg_eq'
      }, {
        value: 'avg[>]',
        label: '期间T的平均值大于N',
        component: 'avg_gt'
      }, {
        value: 'avg[<]',
        label: '期间T的平均值小于N',
        component: 'avg_lt'
      }, {
        value: 'avg[#]',
        label: '期间T的平均值不等于N',
        component: 'avg_neq'
      }, {
        value: 'diff[=]',
        label: '最末和之前的值的差, 则 N = 1, 0 - 除外',
        component: 'diff_eq'
      }
      ],
      // 默认严重等级
      optionValue: '2',
      // 默认只告警一次
      switchValue: '1',
      // 默认启用
      switchStatus: '1',
      dialogFormVisible: false,
      templateSelectVisible: true,
      formLabelWidth: '150px',
      templateOrServerGroupSelectModel: [],
      ItemSelectModel: null,
      // 默认函数
      functionSelectModel: 'last[=]',
      templateOrServerGroupData: [],
      monitorItemListData: [],
      functionListData: [],
      radio: 'template',
      selectTemplateOrServerGroup: '请选择模板',
      // 多个条件之间的逻辑
      specificLogicType: null
    }
  },
  // 动态加载组件
  computed: {
    componentFile() {
      const componentName = this.$store.state.triggerParamComponentName
      return () => import(`./components/${componentName}.vue`)
    }
  },
  methods: {
    changeData(d) {
      this.$store.state.triggerParamComponentName = d
    },
    addCondition(logic) {
      // 打开对话框
      this.dialogFormVisible = true
      this.specificLogicType = logic
      this.fetchData()
    },
    // 获取所有模板列表
    fetchTemplateListData() {
      template_list({ page: 1, size: 99999, order: 'asc' }).then(response => {
        this.templateOrServerGroupData = response.data.items
        this.templateOrServerGroupSelectModel = 1
        // 获取完了刷新监控项
        this.fetchMonitorItemListData()
      })
    },
    // 获取服务器组列表
    fetchServerGroupListData() {
      server_group_list().then(response => {
        this.templateOrServerGroupData = response.data.items
      })
    },
    // 获取监控项
    fetchMonitorItemListData() {
      item_list({ size: 10000, template_id: this.templateOrServerGroupSelectModel }).then(response => {
        // 大坑，必须有value，且上面key不能等于item.id，而是要等于item.value
        // 只有一个参数的函数，箭头函数
        const arr = []
        response.data.items.forEach((value) => {
          value.value = value.id
          arr.push(value)
        })
        this.monitorItemListData = arr
        // 默认选中第一项
        this.ItemSelectModel = arr[0]
        // 默认选中第一项，由于选中的是对象，所以可以用是否是对象，来判断是否第一次加载，如果第一次，就设置为第一项
        if (typeof this.functionSelectModel !== 'object') {
          this.functionSelectModel = this.functionOptions[0]
        }
      })
    },
    fetchData() {
      this.fetchTemplateListData()
      this.fetchServerGroupListData()
    },
    selectType(value) {
      if (value === 'template') {
        this.selectTemplateOrServerGroup = '请选择模板'
        this.fetchTemplateListData()
      } else {
        this.selectTemplateOrServerGroup = '请选择服务器组'
        this.fetchServerGroupListData()
      }
    },
    jumpTriggerList() {
      this.$router.go(-1)
    },
    addExpressionCondition(expression, prefixLogic) {
      this.dialogFormVisible = false
      const functionName = this.$refs.paramFormComp.$refs.paramForm.model.name
      const param1 = this.$refs.paramFormComp.$refs.paramForm.model.param1
      const param2 = this.$refs.paramFormComp.$refs.paramForm.model.param2
      const operator = this.$refs.paramFormComp.$refs.paramForm.model.operator
      const n = this.$refs.paramFormComp.$refs.paramForm.model.n
      let func = ''
      if (!param1 && !param2) {
        func = functionName + '()'
      } else if (param1 && !param2) {
        func = functionName + '(' + param1 + ')'
      } else {
        func = functionName + '(' + param1 + ',' + param2 + ')'
      }
      switch (prefixLogic) {
        case this.logicType.AND:
          this.form.expression = this.form.expression + ' & ' + '{' + expression + '.' + func + '}' + operator + n
          break
        case this.logicType.OR:
          this.form.expression = this.form.expression + ' | ' + '{' + expression + '.' + func + '}' + operator + n
          break
        default:
          this.form.expression = this.form.expression + '{' + expression + '.' + func + '}' + operator + n
      }
    },
    addTrigger(a, b, c, d, e) {
      add_trigger(a, b, c, d, e, this.$route.query.template_id).then(response => {
      }).catch(e => {
        console.log(e)
      })
    }
  }
}

</script>

<style scoped>
  [v-cloak] {
     display:none;
  }
</style>
