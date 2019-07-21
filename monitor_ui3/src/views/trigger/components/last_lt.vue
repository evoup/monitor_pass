<template>
  <div class="param-form">

    <el-form ref="paramForm" :model="paramForm" :rules="paramFormRules">
      <!-- 参数1 -->
      <el-form-item label="最近的(T)连续次数" prop="param1" label-width="150px">
        <el-input v-model="paramForm.param1" />
      </el-form-item>
      <!-- 参数2 -->
      <el-form-item label="多少时间前的" prop="param2" label-width="150px">
        <el-input v-model="paramForm.param2" />
      </el-form-item>
      <el-form-item label="N" prop="n" label-width="150px">
        <el-input v-model="paramForm.n" />
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { validateName } from '../../../lib/validator.js'

class ParamForm {
  constructor() {
    this.param1 = ''
    this.param2 = ''
    this.name = 'last'
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
