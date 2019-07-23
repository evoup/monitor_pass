<template>
  <div class="param-form">
    <el-form ref="paramForm" :model="paramForm" :rules="paramFormRules">
      <el-row>
        <el-col :span="24">
          <!-- 参数1 -->
          <el-form-item label="N" prop="param1" label-width="150px">
            <el-input v-model="paramForm.n" />
          </el-form-item>
        </el-col>
      </el-row>
    </el-form>
  </div>
</template>

<script>
import { validateName } from '../../../lib/validator.js'

class ParamForm {
  constructor() {
    this.param1 = ''
    this.param2 = ''
    this.param3 = ''
    this.name = 'diff'
    this.operator = '='
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
