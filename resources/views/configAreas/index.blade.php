
@extends('layouts.app')
@section('header_title', 'Configuración de areas')
@section('header_subtitle', ': Areas que usará el sistema.')
@section('camino')
  <li class="breadcrumb-item active"> <i class="fa fa-users"></i> Configuración de áreas</li>
@endsection

@section('content')
  <div class="row" id="app">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
      <div class="card card-primary">
        <div class="card-header with-border">
          <h3 class="card-title">Seleccione las áreas que son útiles para su sistema.</h3>
          <div class="card-tools pull-right">
            <span @click="saveCustomTree" class="btn btn-success btn-xs">Guardar configuracíon</span>
          </div>
        </div>
        <div class="card-body" >
          <div class="row">
            {{-- Arbol SOFSE --}}
            <div class="col-6" style="max-height: 500px; overflow-y: auto;">
              <h5 class="pl-2">Áreas del organigrama SOFSE</h5> <hr>
              <div v-if="tree">
                <node-component
                  class="pl-5"
                  :node="tree">
                </node-component>
              </div>
            </div>
            {{-- Arbol del sistema --}}
            <div class="col-6" style="max-height: 500px; overflow-y: auto;">
              <h5 class="pl-2">Areas del sistema</h5> <hr>
              <div v-if="customTree" v-for="root in customTree">
                <node-component
                  class="pl-5"
                  :node="root">
                </node-component>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('page_scripts')
  <script src="{{asset('js/vue.js')}}"></script>
  <script>
    const appUrl = "{{ url('/'); }}";
    let storedConfig = JSON.parse(`{!! $storedConfig !!}`);

    //componente nodo
    const NodeComponent = {
      props: ['node'],
      data: function () {
        return {
          checked: false,
        }
      },
      methods: {
        selectNode(){
          this.node.selected = !this.checked;
          selectNode(this.node.id);
        }
      },
      template: `
      <div style="border-left: solid 1px !important;  border-left-color: #3f6791 !important;">
        <span :id="node.id" >
          <input type="checkbox" v-model="checked" @click="selectNode()">
          @{{node.nombre}}
        </span>

        <node-component
          class="pl-5"
          v-if="node.children && node.children.length"
          v-for="child in node.children"
          :node="child">
        </node-component>
      </div>
      `
    };

    // isntancia de VUE
    const { createApp } = Vue;
    const app = createApp ({
      data() {
        return {
          treeRoot: null,
          tree: null,
          customTree: null,
          customNodes: [],
          relacionesNiveles: [],
          levelsTypes: [],
        }
      },
      methods: {
        async getRoot(){
          const response = await $.ajax({
            type: "GET",
            url: appUrl + '/api-organigrama-get-root',
            dataType: "json"
          });
          this.treeRoot = response;
          this.tree = JSON.parse(JSON.stringify(this.treeRoot));

        },
        async getTree(parentId){
          if (!this.treeRoot) return false;
          let parent = this.findNode(this.tree , parentId);
          let children = this.relacionesNiveles.filter(e=> e.id_nivel_padre == parentId && e.id_nivel_hijo != parentId);
          if(children) {
            parent.children = [];
            for (const child of children) {
              parent.children.push({
                id: child.nivel_hijo.id,
                nombre: child.nivel_hijo.nombre,
                descripcion: child.nivel_hijo.descripcion,
                tipo_id: child.nivel_hijo.tipo_id,
                parent_id: child.id_nivel_padre
              });
              await this.getTree(child.id_nivel_hijo);
            }
          }
        },
        async getRelacionesNiveles(){
          const response = await $.ajax({
            type: "GET",
            url: appUrl + '/api-organigrama-get-relaciones-niveles',
            dataType: "json"
          });
          this.relacionesNiveles = response;
        },
        async getLevelsTypes(){
          const response = await $.ajax({
            type: "GET",
            url: appUrl + '/api-organigrama-get-levels-types',
            dataType: "json",
          });
          this.levelsTypes = response;
        },
        findNode(elem, idToFind){
          var result = null;
          if(elem instanceof Array) {
            for(var i = 0; i < elem.length; i++) {
              result = this.findNode(elem[i], idToFind);
              if (result) { break; }
            }
          }
          else {
            for(var prop in elem) {
              if(prop == 'id') {
                if(elem[prop] == idToFind) {
                  return elem;
                }
              }
              if(elem[prop] instanceof Object || elem[prop] instanceof Array) {
                result = this.findNode(elem[prop], idToFind);
                if (result) { break; }
              }
            }
          }
          return result;
        },
        getCustomTreeNodes(elem=null, result=[], level=0){
          if(elem instanceof Array) {
            for(var i = 0; i < elem.length; i++) {
              this.getCustomTreeNodes(elem[i], result, level+1);
            }
          }
          else
          {
            if(elem.hasOwnProperty('selected') && elem.selected ){
              result.push({
                id: elem.id,
                nombre: elem.nombre,
                descripcion: elem.descripcion,
                selected: elem.selected,
                tipo_id: elem.tipo_id,
                parent_id: elem.parent_id? elem.parent_id: 0,
                level: level+1,
              });
            }

            if(elem.hasOwnProperty('children') && elem.children instanceof Array && elem.children.length){
              this.getCustomTreeNodes(elem.children, result, level+1);
            }
          }
          return result;
        },
        getCustomTree(parent){
          let children = this.customNodes.filter(e=> e.parent_id == parent.id);
          if(children) {
            parent.children = [];
            for (const child of children) {
              let childData = {
                id: child.id,
                nombre: child.nombre,
                descripcion: child.descripcion,
                tipo_id: child.tipo_id,
                tipo: child.tipo,
                parent_id: child.parent_id
              }
              parent.children.push(childData);
              this.getCustomTree(childData);
            }
          }
          return parent;
        },
        async saveCustomTree(){
          if(!this.customTree) return false;
          $.ajaxSetup({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              }
          });
          try{
            const response = await $.ajax({
              type: "POST",
              url: appUrl + '/config-areas',
              data: {tree: JSON.stringify(this.customTree)},
              dataType: "json"
            });
            Swal.fire('Bien!', 'configuración guardada.' , 'success');
          }
          catch(ex){
            console.error('Error al guardar: ',ex.responseJSON.error);
            Swal.fire('Error al guardar', ex.responseJSON.error , 'error');
          }
        }
      },
      async mounted() {
        this.customTree = storedConfig;
        await this.getLevelsTypes();
        await this.getRoot();
        await this.getRelacionesNiveles();
        this.getTree(this.treeRoot.id);
      }
    })
    .component("node-component", NodeComponent)
    .mount('#app')

    function selectNode(parentId){
      let customNodes = app.getCustomTreeNodes(app.tree);
      for (const node of customNodes) {
        let tipo = app.levelsTypes.find(e=>e.id ==  node.tipo_id);
        node.tipo = tipo? tipo.nombre : null;
      }
      app.customNodes = customNodes;
      let sortedCustomNodes = _.sortBy(customNodes, ['level']);
      let lowestLevel = sortedCustomNodes[0].level;
      let root = customNodes.filter(e=>e.level == lowestLevel);
      let customTree = [];
      for (const r of root) {
        let tree = app.getCustomTree(r);
        customTree.push(tree);
      }
      app.customTree = customTree;
    }
  </script>
@endpush
