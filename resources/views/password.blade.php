    @if(!$result)
        <script> alert('패스워드가 올바르지 않습니다.'); </script>
    @endif
<form name="form" method="get" action="">
    @csrf 
    <fieldset>
        <legend>문서를 확인하기 위해서 비밀번호를 입력해주세요.</legend>
        
        <input type="password" name="inputpass" placeholder='비밀번호 입력'/>
        <button type="submit">확인</button>
    </fieldset>
</form>

<style>
body {
    text-align: center;
    display: flex;
    vertical-align: middle;
    justify-content: center;
    align-items: center;
}
form {
    min-width: 30em;
}
</style>
