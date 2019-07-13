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
          <el-row gutter="20">
            <el-col :span="10">
              <el-input v-model="form.expression" placeholder="请输入表达式" type="textarea"/>
            </el-col>
            <el-col :span="10">
              <el-button type="primary" @click="onSubmit">添加条件</el-button>
            </el-col>
          </el-row>
        </el-col>
      </el-form-item>
      <el-form-item label="描述">
        <el-col :span="24">
          <el-row gutter="20">
            <el-col :span="10">
              <el-input v-model="form.desc" placeholder="请输入描述" type="textarea"/>
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
          <el-row gutter="20">
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
          <el-row gutter="20">
            <el-col :span="10">
              <el-switch
                v-model="switchValue1"
                active-value="1"
                inactive-value="0"
              />
            </el-col>
          </el-row>
        </el-col>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="addServer(form.name,form.client, form.jmx, form.snmp, form.idc)">创建</el-button>
        <el-button @click="jumpServerList">取消</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
export default {
  name: 'CreateTrigger',
  data() {
    return {
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
      optionValue: '2',
      switchValue: '1',
      switchValue1: '1'
    }
  },
  methods: {
    jumpServerList() {
      this.$router.go(-1)
    }
  }
}
</script>

<style scoped>
  .el-col {
    border-radius: 4px;
  }
</style>
