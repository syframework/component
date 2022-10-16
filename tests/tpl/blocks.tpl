Nb persons: {FOO_COUNT}
<!-- BEGIN FOO_BLOCK -->
<div>
	Index: {FOO_INDEX}
	Firstname: {FOO_FIRSTNAME}
	Lastname: {FOO_LASTNAME}
	<!-- BEGIN FOO_AGE_BLOCK -->
	Age: {FOO_AGE}
	<!-- ELSE FOO_AGE_BLOCK -->
	Age: N/A
	<!-- END FOO_AGE_BLOCK -->
</div>
<!-- END FOO_BLOCK -->