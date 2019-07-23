<template>
  <div class="param-form">
    <el-form ref="paramForm" :model="paramForm" :rules="paramFormRules">
      <el-row>
        <el-col :span="8">
          <!-- 参数1 -->
          <el-form-item label="最近的(T)" prop="param1" label-width="150px">
            <el-input v-model="paramForm.param1" />
          </el-form-item>
        </el-col>
        <el-col :span="6">
          <el-radio-group v-model="paramForm.param2">
            <el-radio-button label="0">时间</el-radio-button>
            <el-radio-button label="1">次数</el-radio-button>
          </el-radio-group>
        </el-col>
        <el-col :span="4">
          <!-- 参数2 -->
          <el-form-item label="多少时间前的" prop="param2" label-width="100px">
            <el-input v-model="paramForm.param3" />
          </el-form-item>
        </el-col>
        <el-col :span="4">
          <el-form-item label="N" prop="n" label-width="90px">
            <el-input v-model="paramForm.n" />
          </el-form-item>
        </el-col>
        <el-col :span="2"/>
      </el-row>
    </el-form>
  </div>
</template>

<script>
import { validateName } from '../../../lib/validator.js'

class ParamForm {
  constructor() {
    this.param1 = ''
    this.param2 = '0'
    this.param3 = ''
    this.name = 'avg'
    this.operator = '<'
    this.n = '0'
  }

  static getRule() {
    return {
      name: [{ validator: validateName, trigger: 'blur' }]
    }
  }
}

export default {
  data() {
    return {
      paramForm: new ParamForm(),
      paramFormRules: ParamForm.getRule()
    }
  }
}
</script>

<style>
  .param-form {}
  .param-form input {
    width: 50px;
  }
  [v-cloak] {
    display:none;
  }
</style>
